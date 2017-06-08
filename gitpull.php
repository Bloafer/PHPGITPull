<?php
define("PHPGITPULL_VERSION", "0.0.2");

$gitConfigFile = __DIR__ . "/.git/config";
if(file_exists($gitConfigFile)){
    $gitConfigSection = '[remote "origin"]';
    $configUrl = false;
    $configLines = explode(PHP_EOL, file_get_contents($gitConfigFile));
    $sectionFound = false;
    foreach($configLines as $configLine){
        $trimmedConfigLine = trim($configLine);
        if($sectionFound && substr($trimmedConfigLine, 0, 6)=="url = "){
            $configUrl = substr($trimmedConfigLine, 6);
            break;
        }
        if(substr($trimmedConfigLine, 0, strlen($gitConfigSection))==$gitConfigSection){
            $sectionFound = true;
        }
    }
    if($configUrl){
        if(strstr($configUrl, "://")){
            if(isset($_POST["username"]) && isset($_POST["password"])){
                $urlParts = parse_url($configUrl);
                $newUrl = '';
                $newUrl .= $urlParts["scheme"];
                $newUrl .= '://';
                $newUrl .= urlencode($_POST["username"]);
                $newUrl .= ':';
                $newUrl .= urlencode($_POST["password"]);
                $newUrl .= '@';
                $newUrl .= $urlParts["host"];
                $newUrl .= $urlParts["path"];
                print prePage(execPrint("git pull " . $newUrl));
            }else{
                print loginPage();
            }
        }else{
            print prePage(execPrint("git pull " . $configUrl));
        }
    }else{
        print errorPage("No GIT URL");
    }
}else{
    print errorPage("Not GIT repo");
}

function execPrint($command) {
    $result = array();
    exec($command . " 2>&1", $result);
    return implode(PHP_EOL, $result);
}

function loginPage(){
    $html = '';
    $html .= '<form class="form-horizontal" method="post">';
    $html .= '  <div class="form-group">';
    $html .= '    <label for="username" class="col-sm-2 control-label">GIT Username</label>';
    $html .= '    <div class="col-sm-10">';
    $html .= '    <input type="text" class="form-control" id="username" name="username" placeholder="Username">';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '  <div class="form-group">';
    $html .= '    <label for="password" class="col-sm-2 control-label">GIT Password</label>';
    $html .= '    <div class="col-sm-10">';
    $html .= '    <input type="password" class="form-control" id="password" name="password" placeholder="Password">';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '  <div class="form-group">';
    $html .= '    <div class="col-sm-offset-2 col-sm-10">';
    $html .= '      <input type="submit" class="btn btn-success" value="Pull" />';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '</form>';
    return pageHeader() . $html . pageFooter();
}

function versionCheck(){
    $versionFile = "https://raw.githubusercontent.com/Bloafer/PHPGITPull/master/VERSION";
    $version = PHPGITPULL_VERSION;
    return (version_compare($version, @file_get_contents($versionFile))>=0);
}

function pageHeader(){
    $html = '';
    $html .= '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '  <head>';
    $html .= '    <meta charset="utf-8">';
    $html .= '    <meta http-equiv="X-UA-Compatible" content="IE=edge">';
    $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1">';
    $html .= '    <title>PHP GIT puller</title>';
    $html .= '    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">';
    $html .= '    <!--[if lt IE 9]>';
    $html .= '      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>';
    $html .= '      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>';
    $html .= '    <![endif]-->';
    $html .= '  </head>';
    $html .= '  <body>';
    $html .= '    <div class="container">';
    $html .= '      <div class="row">';
    $html .= '        <div class="col-sm-12">';
    $html .= '        <h1>PHP GIT puller</h1>';
    if(!versionCheck()){
        $html .= '<div class="alert alert-info"><strong>Version update available</strong> There is an update available, please visit https://github.com/Bloafer/PHPGITPull to get the latest version</div>';
    }
    return $html;
}

function pageFooter(){
    $html = '';
    $html .= '        </div>';
    $html .= '      </div>';
    $html .= '    </div>';
    $html .= '  </body>';
    $html .= '</html>';
    return $html;
}

function errorPage($error){
    $html = '';
    $html .= '<div class="alert alert-danger"><strong>Error!</strong> ' . $error . '</div>';
    return pageHeader() . $html . pageFooter();
}

function prePage($text){
    $html = '';
    $html .= '    <pre>' . $text . '</pre>';
    return pageHeader() . $html . pageFooter();
}

