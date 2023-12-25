<?php
namespace Partitech\SonataExtra\EventListener;

use JetBrains\PhpStorm\NoReturn;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Entity\SecFirewallRule;
use Partitech\SonataExtra\Entity\SecStopWord;
use Partitech\SonataExtra\Entity\SecIpRule;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

class FirewallListener
{
    private EntityManagerInterface $entityManager;
    private CacheInterface $cache;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, CacheInterface $cache, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        if ($this->isCli()) {
            return;
        }
        if ($this->isUserAdmin()) {
            return;
        }

        $request = $event->getRequest();
        $rules = $this->getCachedFirewallRules();
        foreach ($rules as $rule) {
            if ($this->applyRule($rule, $request)) {
                break;
            }
        }

    }

    private function applyRule(SecFirewallRule $rule,  $request): bool {
        $matchFound = false;

        switch ($rule->getType()) {
            case 'stop_word':
                $matchFound = $this->checkStaticStopWord($rule, $request);
                break;
            case 'ip':
                $matchFound = $this->checkStaticIp($rule, $request);
                break;
            case 'user_agent':
                $matchFound = $this->checkUserAgent($rule, $request);
                break;
            case 'stop_word_db':
                $matchFound = $this->checkDbStopWord($rule, $request);
                break;
            case 'ip_db':
                $matchFound = $this->checkDbIp($rule, $request);
                break;
        }

        if ($matchFound) {
            $this->handleMatch($rule, "Rule match found ".$rule->getType());
        }

        return $matchFound;
    }

    private function checkStaticStopWord(SecFirewallRule $rule, $request): bool {
        $parameters = $rule->getParameters();
        $source = $rule->getSource();
        $matchMode = $rule->getMatchMode();
        $valuesToCheck = $this->getValuesFromSource($source, $request);

        foreach ($parameters as $parameter) {
            foreach ($valuesToCheck as $value) {
                if ($matchMode == 'equal' && $value == $parameter) {
                    return true;
                } elseif ($matchMode == 'contain' && str_contains($value, $parameter)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkStaticIp(SecFirewallRule $rule, $request): bool {
        $ipsToCheck = $rule->getParameters();
        $clientIp = $request->getClientIp();
        $matchMode = $rule->getMatchMode();
        foreach ($ipsToCheck as $ip) {
            if (($matchMode === 'equal' && $clientIp === $ip) ||
                ($matchMode === 'contain' && str_contains($clientIp, $ip))) {
                return true;
            }
        }
        return false;
    }

    private function checkUserAgent(SecFirewallRule $rule, $request): bool {
        $userAgentsToCheck = $rule->getParameters();
        $currentUserAgent = $request->headers->get('User-Agent');
        $matchMode = $rule->getMatchMode();

        foreach ($userAgentsToCheck as $userAgent) {
            if (($matchMode === 'equal' && $currentUserAgent === $userAgent) ||
                ($matchMode === 'contain' && str_contains($currentUserAgent, $userAgent))) {
                return true;
            }
        }
        return false;
    }

    private function checkDbStopWord(SecFirewallRule $rule, $request): bool {
        $matchMode = $rule->getMatchMode();
        $source = $rule->getSource();
        $stopWordsArray = $this->getStopWordsFromCache();

        $valuesToCheck = $this->getValuesFromSource($source, $request);
        foreach ($stopWordsArray as $stopWord) {
            foreach ($valuesToCheck as $value) {
                if (($matchMode === 'equal' && $value === $stopWord) ||
                    ($matchMode === 'contain' && str_contains($value, $stopWord))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getStopWordsFromCache(): array {
            return $this->cache->get('stop_words_cache', function (ItemInterface $item) {
                $item->expiresAfter(3600); // Cache expiration
                $stopWords = $this->entityManager->getRepository(SecStopWord::class)->findAll();
                return array_map(fn($sw) => $sw->getWord(), $stopWords);
            });
    }

    private function checkDbIp(SecFirewallRule $rule, $request): bool {
        $clientIp = $request->getClientIp();
        $ipsFromDb = $this->getIpsFromCache();
        if (in_array($clientIp, $ipsFromDb)) {
            return true;
        }
        return false;
    }

    private function getIpsFromCache(): array {
        return $this->cache->get('ip_rules_cache', function (ItemInterface $item) {
            $item->expiresAfter(3600); // Cache expiration
            $ipRules = $this->entityManager->getRepository(SecIpRule::class)->findAll();
            return array_map(fn($ipRule) => $ipRule->getIp(), $ipRules);
        });
    }

    public function isCli(): bool {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    private function getValuesFromSource(string $source, $request): array {

        switch ($source) {
            case 'get':
                $values = [];
                $getData = $request->query->all();
                $this->extractValuesRecursively($getData, $values);
                return $values;
            case 'post':
                $values = [];
                $postData = $request->request->all();
                $this->extractValuesRecursively($postData, $values);
                return $values;
            case 'header':
                return $request->headers->all();
            default:
                return [];
        }
    }

    #[NoReturn]
    private function handleMatch(SecFirewallRule $rule, string $message): void {
        die('Firewall detection : '.$message);
    }

    private function getCachedFirewallRules(): array {
        return $this->cache->get('firewall_rules_cache', function (ItemInterface $item) {
            $item->expiresAfter(3600*24); // Expiration du cache après 24 heure
            return $this->entityManager->getRepository(SecFirewallRule::class)->findAll();
        });
    }

    private function isUserAdmin()
    {
        $user = $this->security->getUser();
        if ($user) {
            return in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ;
        }

        return false;
    }

    private function extractValuesRecursively($data, &$values)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->extractValuesRecursively($value, $values);
            }
        } else {
            $values[] = $data;
        }
    }
}