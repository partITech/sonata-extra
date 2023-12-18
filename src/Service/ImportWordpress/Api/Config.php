<?php
    
    namespace Partitech\SonataExtra\Service\ImportWordpress\Api;
    
    class Config
    {
        public const PER_PAGE = 100;
        private ?string $url   = null;
        private ?string $user  = null;
        private ?string $token = null;
        
        public function setUrl(?string $url)
        : Config {
            $this->url = $url;
            return $this;
        }
        
        public function setUser(?string $user)
        : Config {
            $this->user = $user;
            return $this;
        }
        
        public function setToken(?string $token)
        : Config {
            $this->token = $token;
            return $this;
        }
        
        /**
         * @return string
         */
        public function getUrl()
        : string
        {
            return $this->url;
        }
        
        /**
         * @return string
         */
        public function getUser()
        : string
        {
            return $this->user;
        }
        
        /**
         * @return string
         */
        public function getToken(): string
        {
            return $this->token;
        }
        
        

    }