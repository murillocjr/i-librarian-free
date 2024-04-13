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
        $myfile = fopen("/tmp/tmp.txt", "w") or die("Unable to open file!");
        for($i = 1; $i < 100; ++$i) {
            $item_id = $i;

            $pdfpath = $model->idToPdfPath($item_id);


            if (file_exists($pdfpath)) {
                $pdf_obj = new Pdf($this->di, $pdfpath);
                $metadata = $pdf_obj->info();

                if $metadata["title"] {
                    $newdata = ['id' => $item_id, 'title' => $metadata["title"], 'page_count' => $metadata["page_count"], 'bibtex_type' => "book"];
                    $model->update($newdata);
                    $txt = $i."|ok\n";
                } else {
                    $txt = $i."|no-metadata-title\n";
                }
            } else {
                $txt = $i."|file_missing\n";
            }
            fwrite($myfile, $txt);
        }
        ////////////
        $view = new DefaultView($this->di);
        return $view->main(['info' => "Hi 9" ]);
    }
}
