<?php

    use VK\Client\VKApiClient;

    class VkService
    {

        private $access_token;
        private $group_id;
        private $api;

        /**
         * VkApiClass constructor.
         * @param string $key
         * @param string $group_id
         */
        public function __construct($key, $group_id)
        {
            $this->access_token = $key;
            $this->group_id = $group_id;
            $this->api = new VKApiClient('5.95');
        }

        public function getConversations()
        {
            try {
                return $this->api->messages()->getConversations($this->access_token, array(
                    'filter' => 'all',
                    'group_id' => $this->group_id
                ));
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        public function getMessagesByConversation($opponentId)
        {

            try {
                return $this->api->messages()->getHistory($this->access_token, array(
                    'user_id' => $opponentId,
                    'group_id' =>$this->group_id
                ));
            } catch (Exception $e) {
                return $e->getMessage();
            }

        }

    }
