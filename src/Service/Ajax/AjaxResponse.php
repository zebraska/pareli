<?php

namespace App\Service\Ajax;

use Symfony\Component\HttpFoundation\Response;

class AjaxResponse
{
    private $response;
    private $views;
    private $flashMessageView;
    private $closeModal;
    private $redirectTo;
    private $bundleName;

    public function __construct($bundleName)
    {
        $this->response = new Response();
        $this->response->headers->set('Content-Type', 'application/json');
        $this->views = [];
        $this->bundleName = $bundleName;
        $this->closeModal = true;
        $this->redirectTo = false;
    }

    public function addView(String $view, String $target)
    {
        array_push($this->views,[
            'view' => $view,
            'target' => $target,
        ]);
    }

    public function setFlashMessageView(String $flashMessageView)
    {
        $this->flashMessageView = $flashMessageView;
    }

    public function setCloseModal(bool $closeModal)
    {
        $this->closeModal = $closeModal;
    }

    public function setRedirectTo(string $redirectTo)
    {
        $this->redirectTo = $redirectTo;
    }

    public function generateContent(): Response
    {
        $this->response->setContent(json_encode([
            'views' => $this->views,
            'flashMessage' => $this->flashMessageView,
            'closeModal' => $this->closeModal,
            'redirectTo' => $this->redirectTo,
            'bundleName' => $this->bundleName,
        ]));

        return $this->response;
    }
}
