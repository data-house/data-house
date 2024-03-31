<?php

namespace App\Analytics\Drivers;

use App\Analytics\Contracts\Driver;
use App\Models\Preference;
use Illuminate\Contracts\Support\Htmlable;

class MatomoDriver implements Driver
{

    public function __construct(protected array $config = [])
    {
        
    }

    protected function isConfigured(): bool
    {
        return !is_null($this->getHost()) && !is_null($this->getSiteId());
    }

    protected function getHost()
    {
        return $this->config['host'] ?? null;
    }

    protected function getSiteId()
    {
        return $this->config['site_id'] ?? null;
    }

    protected function getTrackerEndpoint()
    {
        return $this->config['tracker_endpoint'] ?? 'matomo.php';
    }
    protected function getTrackerScript()
    {
        return $this->config['tracker_script'] ?? 'matomo.js';
    }

    protected function getTrackerConfig($key)
    {
        return $this->config['tracking'][$key] ?? null;
    }


    public function trackerCode(): Htmlable
    {
        if(!$this->isConfigured()){
            return str('')->toHtmlString();
        }

        if(auth()->guest() && !$this->getTrackerConfig('guest')){
            // tracking for guest users not enabled
            return str('')->toHtmlString();
        }
        
        if(!auth()->guest() && auth()->user()->hasPreference(Preference::DO_NOT_TRACK, 'yes')){
            // user asked to not be tracked
            return str('')->toHtmlString();
        }

        $userTracking = '';

        if(($this->getTrackerConfig('user') ?? false) && !auth()->guest()){

            $userKey = auth()->user()->getKey();

            $userTracking = "_paq.push(['setUserId', '{$userKey}']);";

            // // User has just logged out, we reset the User ID
            // _paq.push(['resetUserId']);
        }

        return str(<<<"HTML"
            <script>
                var _paq = window._paq = window._paq || [];
                {$userTracking}
                _paq.push(['trackPageView']);
                _paq.push(['trackVisibleContentImpressions', true, 2000]);
                _paq.push(['enableLinkTracking']);
                (function() {
                    var u="//{$this->getHost()}/";
                    _paq.push(['setTrackerUrl', u+'{$this->getTrackerEndpoint()}']);
                    _paq.push(['setSiteId', '{$this->getSiteId()}']);
                    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                    g.async=true; g.src=u+'{$this->getTrackerScript()}'; s.parentNode.insertBefore(g,s);
                })();
            </script>
                
            HTML)->toHtmlString();
    }


}