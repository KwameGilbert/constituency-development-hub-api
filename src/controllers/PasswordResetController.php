<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * PasswordResetController
 * 
 * Handles password reset functionality:
 * - Request password reset OTP (send email)
 * - Verify OTP
 * - Reset password with verified OTP
 */
class PasswordResetController
{
    private AuthService $authService;
    private EmailService $emailService;

    public function __construct(AuthService $authService, EmailService $emailService)
    {
        $this->authService = $authService;
        $this->emailService = $emailService;
    }

    /**
     * Request password reset OTP (send email)
     * POST /auth/password/forgot
     */
    public function requestReset(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $metadata = $this->getRequestMetadata($request);
            
            if (empty($data['email'])) {
                return ResponseHelper::error($response, 'Email is required', 400);
            }

            $user = User::findByEmail($data['email']);
            
            // Always return success (security: don't reveal if email exists)
            if (!$user) {
                return ResponseHelper::success(
                    $response, 
                    'If that email exists, a password reset code has been sent',
                    []
                );
            }

            // Generate 6-digit OTP
            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpHash = hash('sha256', $otp);

            // Delete old tokens for this email
            PasswordReset::deleteForEmail($user->email);

            // Create new OTP token (expires in 15 minutes via created_at check)
            PasswordReset::create([
                'email' => $user->email,
                'token' => $otpHash,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send email with OTP
            $emailSent = $this->emailService->sendPasswordResetOTPEmail($user, $otp);

            // Log audit event
            $this->authService->logAuditEvent(
                $user->id, 
                'password_reset_requested', 
                array_merge($metadata, ['extra' => ['email_sent' => $emailSent]])
            );

            return ResponseHelper::success(
                $response, 
                'If that email exists, a password reset code has been sent',
                []
            );

        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ResponseHelper::error($response, 'Password reset request failed', 500, $e->getMessage());
        }
    }

    /**
     * Verify OTP without resetting password
     * POST /auth/password/verify-otp
     */
    public function verifyOTP(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['email']) || empty($data['otp'])) {
                return ResponseHelper::error($response, 'Email and OTP are required', 400);
            }

            // Find valid token (OTP is stored as hashed token)
            $resetToken = PasswordReset::findValidToken($data['email'], $data['otp']);

            if (!$resetToken) {
                return ResponseHelper::error($response, 'Invalid or expired OTP', 400);
            }

            return ResponseHelper::success($response, 'OTP verified successfully', [
                'verified' => true,
            ]);

        } catch (Exception $e) {
            error_log("OTP verification error: " . $e->getMessage());
            return ResponseHelper::error($response, 'OTP verification failed', 500, $e->getMessage());
        }
    }

    /**
     * Reset password with token
     * POST /auth/password/reset
     */
    public function resetPassword(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $metadata = $this->getRequestMetadata($request);
            
            // Validate input - accept both 'token' and 'otp' field names
            $otp = $data['otp'] ?? $data['token'] ?? null;
            if (empty($data['email']) || empty($otp) || empty($data['password'])) {
                return ResponseHelper::error($response, 'Email, OTP, and new password are required', 400);
            }

            if (strlen($data['password']) < 8) {
                return ResponseHelper::error($response, 'Password must be at least 8 characters', 400);
            }

            // Find valid token (OTP is stored as hashed token)
            $resetToken = PasswordReset::findValidToken($data['email'], $otp);

            if (!$resetToken) {
                return ResponseHelper::error($response, 'Invalid or expired reset token', 400);
            }

            // Find user
            $user = User::findByEmail($data['email']);
            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Update password (automatically hashed by User model)
            $user->password = $data['password'];
            $user->save();

            // Delete all password reset tokens for this email
            PasswordReset::deleteForEmail($data['email']);

            // Log audit event
            $this->authService->logAuditEvent($user->id, 'password_reset_completed', $metadata);

            // Revoke all refresh tokens (force re-login on all devices for security)
            $this->authService->revokeAllUserTokens($user->id);

            // Send password changed confirmation email
            try {
                $this->emailService->sendPasswordChangedEmail($user);
            } catch (Exception $e) {
                // Log but don't fail - notification email is not critical
                error_log('Failed to send password changed email: ' . $e->getMessage());
            }

            return ResponseHelper::success($response, 'Password reset successful. Please login with your new password.', []);

        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ResponseHelper::error($response, 'Password reset failed', 500, $e->getMessage());
        }
    }

    /**
     * Extract metadata from request
     */
    private function getRequestMetadata(Request $request): array
    {
        $serverParams = $request->getServerParams();
        
        return [
            'ip_address' => $serverParams['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent')
        ];
    }
}
