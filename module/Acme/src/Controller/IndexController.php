<?php
namespace Acme\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    // URL http://demo.acme.com:8888/acme_public 
    // maps to Acme\Controller\IndexController::indexAction function
    //
    public function indexAction() {
        print("Acme::IndexController working...");
        return new ViewModel();
    }

    public function addAction() {}
    public function editAction() {}
    public function deleteAction() {}
}