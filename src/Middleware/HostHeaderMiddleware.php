<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to validate Host header and prevent Host Header Injection attacks.
 *
 * In production, this middleware ensures that App.fullBaseUrl is configured
 * and validates incoming Host headers against it. This prevents attackers
 * from manipulating password reset links and other security-critical URLs.
 *
 * @see https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/17-Testing_for_Host_Header_Injection
 */
class HostHeaderMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and validate the Host header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Configure::read('debug')) {
            return $handler->handle($request);
        }

        $fullBaseUrl = Configure::read('App.fullBaseUrl');
        if (!$fullBaseUrl) {
            throw new InternalErrorException(
                'SECURITY: App.fullBaseUrl is not configured. ' .
                'This is required in production to prevent Host Header Injection attacks. ' .
                'Set APP_FULL_BASE_URL environment variable or configure App.fullBaseUrl in config/app.php',
            );
        }

        $configuredHost = parse_url($fullBaseUrl, PHP_URL_HOST);
        $requestHost = $request->getUri()->getHost();

        // "www.example.com" and "example.com" are treated as the same site, since
        // shared hosting usually serves both.
        $normalize = fn(string $host): string => preg_replace('/^www\./', '', strtolower($host));
        if ($configuredHost && $requestHost && $normalize($configuredHost) !== $normalize($requestHost)) {
            throw new BadRequestException(sprintf(
                'Invalid Host header. Request host "%s" does not match configured application host "%s" '
                . '(set $siteUrl in config/app_local.php to the address you open the app at).',
                $requestHost,
                $configuredHost,
            ));
        }

        return $handler->handle($request);
    }
}
