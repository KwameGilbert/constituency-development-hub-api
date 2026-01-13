<?php

/**
 * Service Container Registration
 * 
 * Registers all services, controllers, and middleware with the DI container
 * For Constituency Development Hub
 */

use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Services\VerificationService;
use App\Services\UploadService;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\PasswordResetController;
use App\Controllers\ProfileController;
use App\Controllers\DashboardController;
use App\Controllers\AdminDataController;

use App\Controllers\BlogPostController;
use App\Controllers\ConstituencyEventController;
use App\Controllers\HeroSlideController;
use App\Controllers\ProjectController;
use App\Controllers\IssueReportController;
use App\Controllers\AgentController;
use App\Controllers\OfficerController;
use App\Controllers\TaskForceController;
use App\Controllers\WebAdminController;
use App\Controllers\SectorController;
use App\Controllers\FAQController;
use App\Controllers\CommunityStatController;
use App\Controllers\ContactInfoController;
use App\Controllers\NewsletterController;
use App\Controllers\AnnouncementController;
use App\Controllers\EmploymentJobController;
use App\Controllers\CommunityIdeaController;
use App\Controllers\OfficerReportsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\JsonBodyParserMiddleware;

return function ($container) {
    
    // ==================== SERVICES ====================
    
    $container->set(EmailService::class, function () {
        return new EmailService();
    });

    $container->set(SMSService::class, function () {
        return new SMSService();
    });
    
    $container->set(AuthService::class, function () {
        return new AuthService();
    });
    
    $container->set(PasswordResetService::class, function ($container) {
        return new PasswordResetService($container->get(EmailService::class));
    });
    
    $container->set(VerificationService::class, function ($container) {
        return new VerificationService($container->get(EmailService::class));
    });

    // Notification System Services
    $container->set(\App\Services\NotificationQueue::class, function () {
        return new \App\Services\NotificationQueue();
    });

    $container->set(\App\Services\TemplateEngine::class, function () {
        return new \App\Services\TemplateEngine();
    });

    $container->set(UploadService::class, function () {
        return new UploadService();
    });

    $container->set(\App\Services\NotificationService::class, function ($container) {
        return new \App\Services\NotificationService(
            $container->get(EmailService::class),
            $container->get(SMSService::class),
            $container->get(\App\Services\NotificationQueue::class),
            $container->get(\App\Services\TemplateEngine::class)
        );
    });

    $container->set(\Psr\Http\Message\ResponseFactoryInterface::class, function () {
        return new \Slim\Psr7\Factory\ResponseFactory();
    });
    
    // ==================== CONTROLLERS ====================
    
    // Auth & User Controllers
    $container->set(AuthController::class, function ($container) {
        return new AuthController($container->get(AuthService::class));
    });
    
    $container->set(UserController::class, function ($container) {
        return new UserController($container->get(AuthService::class));
    });

    $container->set(ProfileController::class, function ($container) {
        return new ProfileController($container->get(UploadService::class));
    });

    $container->set(DashboardController::class, function () {
        return new DashboardController();
    });

    $container->set(AdminDataController::class, function () {
        return new AdminDataController();
    });
    
    $container->set(PasswordResetController::class, function ($container) {
        return new PasswordResetController(
            $container->get(AuthService::class),
            $container->get(EmailService::class)
        );
    });



    // CMS Controllers with UploadService
    $container->set(BlogPostController::class, function ($container) {
        return new BlogPostController(
            $container->get(UploadService::class)
        );
    });

    $container->set(ConstituencyEventController::class, function ($container) {
        return new ConstituencyEventController(
            $container->get(UploadService::class)
        );
    });

    $container->set(HeroSlideController::class, function ($container) {
        return new HeroSlideController(
            $container->get(UploadService::class)
        );
    });

    $container->set(ProjectController::class, function ($container) {
        return new ProjectController(
            $container->get(UploadService::class)
        );
    });

    $container->set(IssueReportController::class, function ($container) {
        return new IssueReportController(
            $container->get(UploadService::class)
        );
    });

    // Role-based Controllers with AuthService and UploadService
    $container->set(AgentController::class, function ($container) {
        return new AgentController(
            $container->get(AuthService::class),
            $container->get(UploadService::class)
        );
    });

    $container->set(OfficerController::class, function ($container) {
        return new OfficerController(
            $container->get(AuthService::class),
            $container->get(UploadService::class)
        );
    });

    $container->set(TaskForceController::class, function ($container) {
        return new TaskForceController(
            $container->get(AuthService::class),
            $container->get(UploadService::class)
        );
    });

    $container->set(WebAdminController::class, function ($container) {
        return new WebAdminController(
            $container->get(AuthService::class),
            $container->get(UploadService::class)
        );
    });

    // Other CMS Controllers
    $container->set(SectorController::class, function () {
        return new SectorController();
    });

    $container->set(FAQController::class, function () {
        return new FAQController();
    });

    $container->set(CommunityStatController::class, function () {
        return new CommunityStatController();
    });

    $container->set(ContactInfoController::class, function () {
        return new ContactInfoController();
    });

    $container->set(NewsletterController::class, function () {
        return new NewsletterController();
    });

    // New Admin Dashboard Feature Controllers
    $container->set(AnnouncementController::class, function () {
        return new AnnouncementController();
    });

    $container->set(EmploymentJobController::class, function () {
        return new EmploymentJobController();
    });

    $container->set(CommunityIdeaController::class, function () {
        return new CommunityIdeaController();
    });

    $container->set(OfficerReportsController::class, function () {
        return new OfficerReportsController();
    });
    
    // ==================== MIDDLEWARES ====================
    
    $container->set(AuthMiddleware::class, function ($container) {
        return new AuthMiddleware($container->get(AuthService::class));
    });
    
    $container->set(RateLimitMiddleware::class, function () {
        return new RateLimitMiddleware();
    });
    
    $container->set(JsonBodyParserMiddleware::class, function () {
        return new JsonBodyParserMiddleware();
    });

    
    return $container;
};
