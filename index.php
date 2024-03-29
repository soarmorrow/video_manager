<!DOCTYPE html>
<?php

/*
NOTE : Some of the code has been trimmed due to security reasons. 
*/

ini_set('max_execution_time', 60 * 60 * 2);
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');
ini_set('max_input_time', 5000);
ini_set('memory_limit', '1000M');

require_once "./class/config.class.php";
require_once "./class/driver.class.php";
require_once "./class/functions.class.php";

define('APPLICATION_PATH', __DIR__);

$config = new Config();
$settings = $config->getDBAccess();
try {
    $db = new PDO(Driver::loadDriver($settings['db']['type'], $settings['db']['host'], $settings['db']['db_name'], $settings['db']['path']), $settings['db']['username'], $settings['db']['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    echo $ex->getMessage();
    exit;
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Video upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="custom video uploader">
    <meta name="author" content="SoarMorrow Solutions">

    <!--link rel="stylesheet/less" href="less/bootstrap.less" type="text/css" /-->
    <!--link rel="stylesheet/less" href="less/responsive.less" type="text/css" /-->
    <!--script src="js/less-1.3.3.min.js"></script-->
    <!--append ‘#!watch’ to the browser URL, then refresh the page. -->

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="//cdn.datatables.net/plug-ins/725b2a2115b/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <![endif]-->

          <!-- Fav and touch icons -->
          <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/apple-touch-icon-144-precomposed.png">
          <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/apple-touch-icon-114-precomposed.png">
          <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/apple-touch-icon-72-precomposed.png">
          <link rel="apple-touch-icon-precomposed" href="img/apple-touch-icon-57-precomposed.png">
          <script type="text/javascript">
              var path = '<?php echo getPath(); ?>';
          </script>
      </head>

      <body>

        <?php
        $post = filter_input_array(INPUT_POST);
        if (isset($post['submit']) && $post['submit']) {
            if (!empty($_FILES['upload']['name'][0])) {
                $error = array();
                $allowed = array(
                    'mp4', 'mkv', 'ogg', '3gp', 'avi', 'webm', 'ogv', 'flv', 'mpeg', 'mpg', 'wav'
                    );
                $count = count($_FILES['upload']['name']);
                $server = filter_input_array(INPUT_SERVER);
                for ($i = 0; $i < $count; $i++) {
                    $target_path = APPLICATION_PATH . "/uploads/";
                    $relativePath = "uploads/";
                    $ext = end(explode('.', basename($_FILES['upload']['name'][$i])));
                    if ($ext && in_array(strtolower($ext), $allowed)) {
                        $size = $_FILES['upload']['size'][$i];
                        if ($size < 50000000) {
                            $filname = basename($_FILES['upload']['name'][$i]);
                            $hash = hash('sha256', uniqid());
                            $stagename =  $hash. "." . $ext;
                            $target_path .= $stagename;$relativePath.=$stagename;
                            if (move_uploaded_file($_FILES['upload']['tmp_name'][$i], $target_path)) {
                                $statement = $db->prepare("INSERT INTO `uploads` (`title`,`type`,`size`,`path`,`hash`,`status`,`trash`) VALUES (:title,:type,:size,:path,:hash,1,0)");
                                $data = array(
                                    ':title' => basename($_FILES['upload']['name'][$i]),
                                    ':type' => $ext,
                                    ':size' => $_FILES['upload']['size'][$i],
                                    ':path' => $relativePath,
                                    ':hash' => $hash
                                    );
                                $statement->execute($data);
                                $success[] = $filname . " has been uploaded successfully";
                                chmod($target_path, 0777);
                            } else {
                                $error[] = "There was an error uploading the file " . $filname . ", please try again!";
                            }
                        } else {
                            $error[] = "File size is more than 50MB";
                        }
                    } else {
                        $error[] = "Invalid file type {$ext}, Allowed types are <i>" . implode(', ', $allowed) . '</i>';
                    }
                }
            }
            if(isset($issetYT) && $issetYT == true){
                $statement = $db->prepare("INSERT INTO `uploads` (`title`,`type`,`size`,`path`,`hash`,`status`,`trash`) VALUES (:title,:type,:size,:path,:hash,1,0)");
                $data = array(
                    ':title' => addslashes(strip_tags(trim($post['yt_title']))),
                    ':type' => 'youtube',
                    ':size' => 0,
                    ':path' => trim($post['yt']),
                    ':hash' => md5(uniqid())
                    );
                $statement->execute($data);
                $success[] = $post['yt_title'] . " has been uploaded successfully";
                unset($issetYT);
            }
            if(empty($post['yt']) && empty($post['yt_title']) && empty($_FILES['upload']['name'][0])){
                $error[] = "I have no idea what are you trying to upload !!";
            }
        }
        ?>
        <div class="container">
            <div class="copy_modal_overlay"></div>
            <div class="copy_modal">
                <textarea class="form-control" id="copy-content" readonly="readonly">empty workarea!</textarea>
                <button class="btn btn-danger pull-right close_copy"><i class='glyphicon glyphicon-remove'></i> close</button>
            </div>
            <div class="row clearfix">
                <div class="col-md-12 column">
                    <blockquote>
                        <h2>Select videos to be uploaded</h2>
                    </blockquote>
                    <?php
                    if (!empty($error)) {
                        ?>
                        <div class="alert alert-danger">
                            <strong>Error !</strong> Please check following errors
                            <ul>
                                <?php
                                foreach ($error as $err) {
                                    echo '<li>' . $err . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                    if (!empty($success)) {
                        ?>
                        <div class="alert alert-success">
                            <strong>Success</strong> videos uploaded successfully
                            <ul>
                                <?php
                                foreach ($success as $succ) {
                                    echo '<li>' . $succ . '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="well well-sm">
                        <form role="form" class="form-horizontal" method="post" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="" class="control-label col-sm-2">Upload</label><small> (Max size 50MB)</small>
                                <div class="col-sm-8 input-group">
                                    <span class="input-group-addon"><i class="fa fa-file-video-o"></i></span>
                                    <input type="file" name="upload[]" class="form-control upload-item" data-item="0">
                                </div>
                            </div>
                            <div class="form-group yt-link">
                                <label for="yt" class="control-label col-sm-2">Youtube</label>
                                <div class="col-sm-4 input-group">
                                    <span class="input-group-addon"><i class="fa fa-youtube"></i></span>
                                    <input type="text" id="yt-title" name="yt_title" class="form-control input-md" placeholder="Some title here">
                                </div>
                                <div class="col-sm-4 input-group">
                                    <span class="input-group-addon"><i class="fa fa-youtube-play"></i></span>
                                    <input type="url" id="yt" name="yt" class="form-control input-md" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX">
                                </div>
                            </div>
                            <div class="btn-group pull-right">
                                <button type="button" id="add" class="btn btn-default"><i class='glyphicon glyphicon-plus'></i> Add more</button>
                                <button type="reset" class="btn btn-danger"><i class='glyphicon glyphicon-refresh'></i> Reset uploads</button>
                                <button type="submit" name="submit" value="submit" class="btn btn-success"><i class='glyphicon glyphicon-upload'></i> Submit</button>
                            </div>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
                <div class="well well-sm">
                    <div class="table-responsive">
                        <table id="videoTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>sl.no</th>
                                    <th>title</th>
                                    <th>size<small>(in MB)</small></th>
                                    <th>link</th>
                                    <th>created at</th>
                                    <th>action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statement = $db->prepare("SELECT * FROM `uploads` WHERE 1 ORDER BY `id` DESC");
                                $statement->execute();
                                $result = $statement->fetchAll();
                                $i = 0 ;
                                foreach ($result as $value) {
                                    $size = ((intval($value['size']/1024/1024)==0)?round(floatval($value['size']/1024/1024),3):intval($value['size']/1024/1024));
                                    $print_size = ($size == 0)?'-':$size;
                                    $path = "<a href='{$value['path']}?".uniqid()."' class='btn btn-link'>".mb_substr($value['title'], 0,20)."...</a>";
                                    
                                    echo "<tr>";
                                    printf("<td>%d</td>".$print_size."</td><td>%s</td><td>%s</td><td><button data-id='".$value['id']."' data-hash='".$value['hash']."' class='btn btn-xs btn-success embed'><i class='glyphicon glyphicon-edit'></i> embed</button></td>",++$i,$title,$path,date('d M Y', strtotime($value['created_at'])));
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="js/ZeroClipboard.js"></script>
        <script type="text/javascript" src="js/scripts.js"></script>
    </body>
    </html>
