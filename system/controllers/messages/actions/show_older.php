<?php

class actionMessagesShowOlder extends cmsAction {

    /**
     * @var array Описание правил валидации входных данных
     */
    public $request_params = [
        'contact_id' => [
            'default' => 0,
            'rules'   => [
                ['required'],
                ['digits']
            ]
        ],
        'message_id' => [
            'default' => 0,
            'rules'   => [
                ['required'],
                ['digits']
            ]
        ]
    ];

    public function run() {

        if (empty($this->options['is_enable_pm'])) {
            return cmsCore::error404();
        }

        $contact_id = $this->request->get('contact_id');
        $message_id = $this->request->get('message_id');

        $contact = $this->model->getContact($this->cms_user->id, $contact_id);
        if (!$contact) {
            return $this->cms_template->renderJSON(['error' => true]);
        }

        $messages = $this->model->filterLt('id', $message_id)->
                limit($this->options['limit'] + 1)->
                getMessages($this->cms_user->id, $contact_id);

        if (count($messages) > $this->options['limit']) {
            $has_older = true;
            array_shift($messages);
        } else {
            $has_older = false;
        }

        $this->cms_template->renderJSON([
            'error'     => ($messages ? false : true),
            'html'      => ($messages ? $this->cms_template->render('message', array(
                'messages'  => $messages,
                'last_date' => '',
                'user'      => $this->cms_user
            ), new cmsRequest([], cmsRequest::CTX_INTERNAL)) : ''),
            'has_older' => $has_older,
            'older_id'  => $messages[0]['id']
        ]);
    }

}
