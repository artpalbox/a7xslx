<?php

namespace A7XSLX;

class App {

    public static function run($action) {
        switch ($action) {
            case 'list':
                (new FileList())->render();
                break;
            case 'upload':
                (new FileUpload())->render();
                break;
            case 'edit':
                (new FileEdit())->render();
                break;
            case 'view':
                (new FileView())->render();
                break;
            case 'download':
                (new FileDownload())->handle();
                break;
            case 'api':
                (new API())->handle();
                break;
            default:
                echo '<p>Invalid action.</p>';
                break;
        }
    }
}