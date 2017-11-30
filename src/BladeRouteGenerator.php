<?php

namespace Tightenco\Ziggy;

use Illuminate\Routing\Router;
use function array_key_exists;

class BladeRouteGenerator
{

    private $baseDomain;
    private $basePort;
    private $baseUrl;
    private $baseProtocol;
    private $router;
    public  $routePayload;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getRoutePayload($group = false, $additionalRoutes = null)
    {
        return RoutePayload::compile($this->router, $group, $additionalRoutes);
    }

    public function generate($group = false, $additionalRoutes = null)
    {
        $this->prepareDomain();

        $json = $this->getRoutePayload($group, $additionalRoutes)->toJson();

        $routeFunction = file_get_contents($this->getRouteFilePath());

        return <<<EOT
<script type="text/javascript">
    var Ziggy = {
        namedRoutes: $json,
        baseUrl: '{$this->baseUrl}',
        baseProtocol: '{$this->baseProtocol}',
        baseDomain: '{$this->baseDomain}',
        basePort: {$this->basePort}
    };

    $routeFunction
</script>
EOT;
    }

    private function getRouteFilePath()
    {
        $isMin = app()->isLocal() ? '' : '.min';
        return __DIR__ . "/../dist/js/route{$isMin}.js";
    }

    private function prepareDomain()
    {
        $url = url('/');
        $parsedUrl = parse_url($url);

        $this->baseUrl = $url . '/';
        $this->baseProtocol = array_key_exists('scheme', $parsedUrl) ? $parsedUrl['scheme'] : 'http';
        $this->baseDomain = array_key_exists('host', $parsedUrl) ? $parsedUrl['host'] : '';
        $this->basePort = array_key_exists('port', $parsedUrl) ? $parsedUrl['port'] : 'false';
    }
}
