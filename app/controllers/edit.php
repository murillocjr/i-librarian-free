<?php

namespace LibrarianApp;

use Exception;
use Librarian\Container\DependencyInjector;
use Librarian\Media\Pdf;


class EditController extends AppController {

    /**
     * EditController constructor.
     *
     * @param DependencyInjector $di
     * @throws Exception
     */
    public function __construct(DependencyInjector $di) {

        parent::__construct($di);

        $this->session->close();

        // Authorization.
        $this->authorization->signedId(true);
        $this->authorization->permissions('U');
    }

    /**
     * Main. Item metadata form.
     *
     * @return string
     * @throws Exception
     */
    public function mainAction(): string {

        // Id.
        if (isset($this->get['id']) === false) {

            throw new Exception("id parameter is required", 400);
        }

        $this->validation->id($this->get['id']);

        $model = new ItemModel($this->di);
        $item = $model->get($this->get['id']);

        $view = new EditView($this->di);
        return $view->main($item);
    }

    /**
     * Save updated item metadata.
     *
     * @return string
     * @throws Exception
     */
    public function saveAction(): string {

        // POST request is required.
        if ($this->request->getMethod() !== 'POST') {

            throw new Exception("request method must be POST", 405);
        }

        if (isset($this->post['id']) === false) {

            throw new Exception("id parameter is required", 400);
        }

        $this->validation->id($this->post['id']);

        $model = new ItemModel($this->di);

        ////////////
        $item_id = 1;

        $pdfpath = $model->idToPdfPath($item_id);
        $pdf_obj = new Pdf($this->di, $pdfpath);
        $metadata = $pdf_obj->info();

        $this->post['id'] = $item_id;
        $this->post['title'] = $metadata["title"];


        $myfile = fopen("/tmp/tmp.txt", "w") or die("Unable to open file!");
        $txt = serialize($metadata);
        fwrite($myfile, $txt);
        ////////////

        // $model->update($this->post);

        $view = new DefaultView($this->di);
        return $view->main(['info' => "Hi 8" ]);
    }
}
