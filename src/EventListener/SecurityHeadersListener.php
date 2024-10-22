<?php

namespace Partitech\SonataExtra\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SecurityHeadersListener
{
    const string NONE = 'none';
    const array SECURITY_TYPES = [
        'default-src' => 'default_src',
        'script-src' => 'script_src',
        'style-src' => 'style_src',
        'img-src' => 'img_src',
        'connect-src' => 'connect_src',
        'font-src' => 'font_src',
        'object-src' => 'object_src',
        'media-src' => 'media_src',
        'frame-src' => 'frame_src',
        'child-src' => 'child_src',
        'form-action' => 'form_action',
        'frame-ancestors' => 'frame_ancestors',
        'manifest-src' => 'manifest_src',
        'base-uri' => 'base_uri',
        'sandbox' => 'sandbox',
        'report-uri' => 'report_uri',
        'worker-src' => 'worker_src',
        'navigate-to' => 'navigate_to'
    ];


    public function __construct(private readonly ?array $policies)
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $computedPolicies = null;
        foreach (self::SECURITY_TYPES as $directive => $configKey) {
            if (isset($this->policies[$configKey])) {
                $datas = $this->getDataForKey($configKey);
                if (!is_null($datas)) {
                    $computedPolicies[$configKey] = $directive . ' ' . $datas;
                }
            }
        }
        if (empty($computedPolicies)) {
            return;
        }
        $response->headers->set("Content-Security-Policy", implode('; ', $computedPolicies), false);
    }

    private function getDataForKey(string $configKey): ?string
    {
        $values = null;

        foreach ($this->policies[$configKey] as $data) {
            $values .= ' ' . $data . ' ';
        }
        if (is_null($values)) {
            return null;
        }
        return $values;
    }
}