<?php

declare (strict_types=1);
/**
 * WP AI Client: WP_AI_Client_HTTP_Client class
 *
 * @package WordPress
 * @subpackage AI
 * @since 7.0.0
 */
use Word_Press\Ai_Client\Providers\Http\Contracts\Client_With_Options_Interface;
use Word_Press\Ai_Client\Providers\Http\DTO\Request_Options;
use Word_Press\Ai_Client\Providers\Http\Exception\Network_Exception;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Client\Client_Interface;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Message\Request_Interface;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Message\Response_Factory_Interface;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Message\Response_Interface;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Message\Stream_Factory_Interface;
/**
 * PSR-18 HTTP Client adapter using WordPress HTTP API.
 *
 * Allows WordPress HTTP functions to be used as a PSR-18 compliant HTTP client
 * for the AI Client SDK.
 *
 * @since 7.0.0
 * @internal Intended only to wire up the PHP AI Client SDK to WordPress's HTTP client.
 * @access private
 */
class WP_AI_Client_HTTP_Client implements Client_Interface, Client_With_Options_Interface
{
    /**
     * Response factory instance.
     *
     * @since 7.0.0
     * @var ResponseFactoryInterface
     */
    private $response_factory;
    /**
     * Stream factory instance.
     *
     * @since 7.0.0
     * @var StreamFactoryInterface
     */
    private $stream_factory;
    /**
     * Constructor.
     *
     * @since 7.0.0
     *
     * @param ResponseFactoryInterface $response_factory PSR-17 Response factory.
     * @param StreamFactoryInterface   $stream_factory   PSR-17 Stream factory.
     */
    public function __construct(Response_Factory_Interface $response_factory, Stream_Factory_Interface $stream_factory)
    {
        $this->response_factory = $response_factory;
        $this->stream_factory = $stream_factory;
    }
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @since 7.0.0
     *
     * @param RequestInterface $request The PSR-7 request.
     * @return ResponseInterface The PSR-7 response.
     *
     * @throws NetworkException If the WordPress HTTP request fails.
     */
    public function send_request(Request_Interface $request): Response_Interface
    {
        $args = $this->prepare_wp_args($request);
        $url = (string) $request->get_uri();
        $response = wp_safe_remote_request($url, $args);
        if (is_wp_error($response)) {
            $message = sprintf(
                /* translators: 1: HTTP method (e.g. GET, POST). 2: Request URL. 3: Error message. */
                __('Network error occurred while sending %1$s request to %2$s: %3$s'),
                $request->get_method(),
                $url,
                $response->get_error_message()
            );
            throw new Network_Exception($message);
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }
        return $this->create_psr_response($response);
    }
    /**
     * Sends a PSR-7 request with transport options and returns a PSR-7 response.
     *
     * @since 7.0.0
     *
     * @param RequestInterface $request The PSR-7 request.
     * @param RequestOptions   $options Transport options for the request.
     * @return ResponseInterface The PSR-7 response.
     *
     * @throws NetworkException If the WordPress HTTP request fails.
     */
    public function send_request_with_options(Request_Interface $request, Request_Options $options): Response_Interface
    {
        $args = $this->prepare_wp_args($request, $options);
        $url = (string) $request->get_uri();
        $response = wp_safe_remote_request($url, $args);
        if (is_wp_error($response)) {
            $message = sprintf(
                /* translators: 1: Request URL. 2: Error message. */
                __('Network error occurred while sending request to %1$s: %2$s'),
                $url,
                $response->get_error_message()
            );
            throw new Network_Exception(
                $message,
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                $response->get_error_code() ? (int) $response->get_error_code() : 0
            );
        }
        return $this->create_psr_response($response);
    }
    /**
     * Prepares WordPress HTTP API arguments from a PSR-7 request.
     *
     * @since 7.0.0
     *
     * @param RequestInterface    $request The PSR-7 request.
     * @param RequestOptions|null $options Optional transport options for the request.
     * @return array<string, mixed> WordPress HTTP API arguments.
     */
    private function prepare_wp_args(Request_Interface $request, ?Request_Options $options = null): array
    {
        $args = ['method' => $request->get_method(), 'headers' => $this->prepare_headers($request), 'body' => $this->prepare_body($request), 'httpversion' => $request->get_protocol_version(), 'blocking' => true];
        if (null !== $options) {
            if (null !== $options->get_timeout()) {
                $args['timeout'] = $options->get_timeout();
            }
            if (null !== $options->get_max_redirects()) {
                $args['redirection'] = $options->get_max_redirects();
            }
        }
        return $args;
    }
    /**
     * Prepares headers for WordPress HTTP API.
     *
     * @since 7.0.0
     *
     * @param RequestInterface $request The PSR-7 request.
     * @return array<string, string> Headers array for WordPress HTTP API.
     */
    private function prepare_headers(Request_Interface $request): array
    {
        $headers = [];
        foreach ($request->get_headers() as $name => $values) {
            $headers[(string) $name] = implode(', ', $values);
        }
        return $headers;
    }
    /**
     * Prepares request body for WordPress HTTP API.
     *
     * @since 7.0.0
     *
     * @param RequestInterface $request The PSR-7 request.
     * @return string|null The request body.
     */
    private function prepare_body(Request_Interface $request): ?string
    {
        $body = $request->get_body();
        if ($body->get_size() === 0) {
            return null;
        }
        if ($body->is_seekable()) {
            $body->rewind();
        }
        return (string) $body;
    }
    /**
     * Creates a PSR-7 response from a WordPress HTTP response.
     *
     * @since 7.0.0
     *
     * @param array<string, mixed> $wp_response WordPress HTTP API response array.
     * @return ResponseInterface PSR-7 response.
     */
    private function create_psr_response(array $wp_response): Response_Interface
    {
        $status_code = wp_remote_retrieve_response_code($wp_response);
        $reason_phrase = wp_remote_retrieve_response_message($wp_response);
        $headers = wp_remote_retrieve_headers($wp_response);
        $body = wp_remote_retrieve_body($wp_response);
        $response = $this->response_factory->create_response((int) $status_code, $reason_phrase);
        if ($headers instanceof WP_HTTP_Requests_Response) {
            $headers = $headers->get_headers();
        }
        if (is_array($headers) || $headers instanceof Traversable) {
            foreach ($headers as $name => $value) {
                $response = $response->with_header($name, $value);
            }
        }
        if (!empty($body)) {
            $stream = $this->stream_factory->create_stream($body);
            $response = $response->with_body($stream);
        }
        return $response;
    }
}