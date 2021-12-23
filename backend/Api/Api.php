<?php

namespace FileCloud\Api;


use FileCloud\Config\Config;
use FileCloud\Controllers\AuthController;
use FileCloud\Controllers\DownloadController;
use FileCloud\Controllers\FileController;
use FileCloud\Controllers\UploadController;
use FileCloud\Controllers\UserController;
use FileCloud\Utils\Captcha;

class Api
{

    public function __call($functionName, $args)
    {
        echo '{"error":"errorMethod"}';
    }

    public function loginUser(): void
    {
        $string = <<<XML
<?xml version='1.0' encoding='utf-8'?>
<root>
</root>
XML;
        $doc = simplexml_load_string($string);
        if (isset($_POST["user"], $_POST["passwd"], $_POST["captcha"]) && !AuthController::isLogin() &&
            preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,15}$/', $_POST["user"]) &&
            preg_match('/^[a-zA-Z]\w{5,17}$/', $_POST["passwd"]) &&
            preg_match('/^\d{5}$/', $_POST["captcha"])) {
            $doc->addChild("error", AuthController::loginUser($_POST["user"], $_POST["passwd"], $_POST["captcha"]));
        } else {
            $doc->addChild("error", '4');
        }
        header("content-type:application/xml;charset=utf-8");
        echo $doc->asXML();
    }

    public function register()
    {
        $string = <<<XML
<?xml version='1.0' encoding='utf-8'?>
<root>
</root>
XML;
        $doc = simplexml_load_string($string);
        if (isset($_POST["user"], $_POST["passwd"]) &&
            preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,15}$/', $_POST["user"]) &&
            preg_match('/^[a-zA-Z]\w{5,17}$/', $_POST["passwd"])) {

            if ($uid = UserController::addUser($_POST["user"], $_POST["passwd"])) {
                if (FileController::addFolder($_POST["user"], 1, $uid)) {
                    $doc->addChild("error", 0);
                    header("content-type:application/xml;charset=utf-8");
                    echo $doc->asXML();
                    return;
                }
            }
            $doc->addChild("error", 1);

        } else {
            $doc->addChild("error", 2);
        }
        header("content-type:application/xml;charset=utf-8");
        echo $doc->asXML();
    }

    public function changePasswd()
    {
        $string = <<<XML
<?xml version='1.0' encoding='utf-8'?>
<root>
</root>
XML;
        $doc = simplexml_load_string($string);
        if (isset($_POST["user"], $_POST["passwd"], $_POST["newPasswd"])) {
            if (UserController::changePassword($_POST["user"], $_POST["passwd"], $_POST["newPasswd"])) {
                $doc->addChild("error", 0);
            } else {
                $doc->addChild("error", 1);
            }
        } else {
            $doc->addChild("error", 2);
        }
        header("content-type:application/xml;charset=utf-8");
        echo $doc->asXML();
    }

    public function newFolder()
    {
        if (isset($_REQUEST['folderName'], $_REQUEST['Parent'])) {
            if (($uid = AuthController::isLogin()) && FileController::isOwnerOfFile($_REQUEST['Parent']) && $_REQUEST['folderName']!="") {
                if (FileController::addFolder($_REQUEST['folderName'], $_REQUEST['Parent'], $uid)) {
                    echo '{"error":"0"}';
                } else {
                    echo '{"error":"1"}';
                }
            } else {
                echo '{"error":"3"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function renameFile()
    {
        if (isset($_REQUEST['newName'], $_REQUEST['fid'])) {
            if (FileController::isOwnerOfFile($_REQUEST['fid'])) {
                if (FileController::renameFile($_REQUEST['fid'], $_REQUEST['newName'])) {
                    echo '{"error":"0"}';
                } else {
                    echo '{"error":"1"}';
                }
            } else {
                echo '{"error":"3"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function delFile()
    {
        if (isset($_REQUEST['fid'])) {
            if (FileController::isOwnerOfFile($_REQUEST['fid'])) {
                if ($_REQUEST['fid'] == $_SESSION['rootPath']) {
                    echo '{"error":"3"}';
                    return;
                }
                if (FileController::delFile($_REQUEST['fid'])) {
                    echo '{"error":"0"}';
                } else {
                    echo '{"error":"1"}';
                }
            } else {
                echo '{"error":"3"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function showFile()
    {
        if (isset($_REQUEST['page'], $_REQUEST['limit'])) {
            if (!isset($_REQUEST['fid']) && $uid = AuthController::isLogin()) {
                $fid = FileController::findUserHome($uid);
            } else if (FileController::isOwnerOfFile($_REQUEST['fid'])) {
                $fid = $_REQUEST['fid'];
            } else {
                echo '{"error":"3"}';
                return;
            }
            if ($result = FileController::showFile($fid, 0, $_REQUEST['page'], $_REQUEST['limit'])) {
                $result['error'] = "0";
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
            } else {
                echo '{"error":"1"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function isExistMD5()
    {
        if (isset($_POST['fileMD5'], $_POST['fileName'], $_POST['parent'])) {
            if (($uid = AuthController::isLogin()) && FileController::isOwnerOfFile($_POST['parent'])) {
                if (UploadController::isExistMD5($_POST['fileMD5'], $_POST['fileName'], $_POST['parent'], $uid)) {
                    echo '{"error":"1"}';
                } else {
                    echo '{"error":"0"}';
                }
            } else {
                echo '{"error":"3"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function uploadFile()
    {
        if (isset($_FILES['file'], $_POST['fileMD5'], $_POST['parent'])) {
            if ($uid = AuthController::isLogin()) {
                if ($error = UploadController::uploadFile($_FILES['file'], $_POST['fileMD5'], $_POST['parent'], $uid)) {
                    echo "{\"error\":\"$error\"}";
                } else {
                    echo '{"error":"0"}';
                }
            } else {
                echo '{"error":"10"}';
            }
        } else {
            echo '{"error":"9"}';
        }
    }

    public function downloadFile()
    {
        if (isset($_REQUEST['fid'])) {
            if ($result = FileController::isOwnerOfFile($_REQUEST['fid'])) {
                if ($result['FileMD5'] == null) {
                    echo '{"error":"4"}';
                } else {
                    DownloadController::getDownload($result['FileMD5'], $result['FileName']);
                }
            } else {
                echo '{"error":"3"}';
            }
        } else {
            echo '{"error":"2"}';
        }
    }

    public function getCaptcha()
    {
        $captcha = new Captcha();
        if (!session_id()) {
            session_set_cookie_params(Config::LifeTime);
            session_start();
        }
        header("Content-type:text/plain;charset=utf-8");
        echo $captcha->getImg();
        $_SESSION['captcha'] = $captcha->getKey();
    }

    public function logout()
    {
        if (!session_id()) session_start();
        unset($_SESSION['uid']);
        unset($_SESSION['rootPath']);
        session_destroy();
        setcookie('PHPSESSID', null, time() - 1);
        echo '{"error":"0"}';
    }

    public function isLogin()
    {
        if (AuthController::isLogin()) {
            echo '{"status":"1"}';
        } else {
            echo '{"status":"0"}';
        }
    }


}