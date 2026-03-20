<?php

declare (strict_types=1);
/**
 * WP AI Client: WP_AI_Client_Discovery_Strategy class
 *
 * @package WordPress
 * @subpackage AI
 * @since 7.0.0
 */
use Word_Press\Ai_Client\Providers\Http\Abstracts\Abstract_Client_Discovery_Strategy;
use Word_Press\Ai_Client_Dependencies\Nyholm\Psr7\Factory\Psr17Factory;
use Word_Press\Ai_Client_Dependencies\Psr\Http\Client\Client_Interface;
/**
 * Discovery strategy for WordPress HTTP client.
 *
 * Registers the WordPress HTTP client adapter with the HTTPlug discovery system
 * so the AI Client SDK can find and use it automatically.
 *
 * @since 7.0.0
 * @internal Intended only to register WordPress's HTTP client so that the PHP AI Client SDK can use it.
 * @access private
 */
class WP_AI_Client_Discovery_Strategy extends Abstract_Client_Discovery_Strategy
{
    /**
     * Creates an instance of the WordPress HTTP client.
     *
     * @since 7.0.0
     *
     * @param Psr17Factory $psr17_factory The PSR-17 factory for creating HTTP messages.
     * @return ClientInterface The PSR-18 HTTP client.
     */
    protected static function create_client(Psr17Factory $psr17_factory): Client_Interface
    {
        return new WP_AI_Client_HTTP_Client($psr17_factory, $psr17_factory);
    }
}