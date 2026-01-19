<?php

use App\Controllers\UploadController;
use App\Middleware\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app): void {
    $container = $app->getContainer();

    $app->group('/v1/admin/upload', function (RouteCollectorProxy $group) use ($container) {
        // Generic upload (matches frontend uploadService)
        $group->post('', [UploadController::class, 'upload']);

        // Get upload info (public)
        $group->get('/info', [UploadController::class, 'getUploadInfo']);

        // Authenticated upload routes
        $group->group('', function (RouteCollectorProxy $authGroup) {
            // Single file uploads
            $authGroup->post('/image', [UploadController::class, 'uploadImage']);
            $authGroup->post('/banner', [UploadController::class, 'uploadBanner']);
            $authGroup->post('/document', [UploadController::class, 'uploadDocument']);
            $authGroup->post('/video', [UploadController::class, 'uploadVideo']);

            // Multiple file uploads
            $authGroup->post('/images', [UploadController::class, 'uploadMultipleImages']);
            $authGroup->post('/documents', [UploadController::class, 'uploadMultipleDocuments']);

            // Delete file
            $authGroup->delete('', [UploadController::class, 'deleteFile']);
        })->add($container->get(AuthMiddleware::class));
    });
};
