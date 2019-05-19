<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="./assets/pickmeup.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="./assets/pickmeup.min.js"></script>
    <script src="./assets/jquery.pickmeup.twitter-bootstrap.js"></script>
    <script>
        $('.date').pickmeup_twitter_bootstrap();
        jQuery(document).ready(function($) {
            pickmeup('.date', {
                format	: 'Y-m-d',
            });
        });
    </script>
</head>

<body>
<div class="container">
    <div class="row">
        <form action="" class="col-sm-12">
            <label>Token</label><br>
            <input class="form-control" type="text" name="token" value="<?=htmlspecialchars(addslashes($_GET['token']))?>"><br>
            <label for="">Group ID</label><br>
            <input class="form-control" type="text" name="groupid" value="<?=htmlspecialchars(addslashes($_GET['groupid']))?>"><br>
            <label> Date</label><br>
            <input class="form-control date" type="text" name="date" value="<?=htmlspecialchars(addslashes($_GET['date']))?>"><br>
            <button>Submit</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="results col-sm-12">

        <?
            if (isset($_GET['date']) && $_GET['date'] != null) {

                require_once '../vendor/autoload.php';
                require_once '../app/VkService.php';
                require_once '../app/Helper.php';


                /**
                 *
                 *  Preparing data
                 *
                 */
//        "182401180";
//        "0b83fd022e5d9be121753066e7f1ce02f7ff3e6556c06b5b0b6b1805c36fea3fa26b16a42c49efa961daf";

                $access_token = htmlspecialchars(addslashes($_GET['token']));
                $group_id = htmlspecialchars(addslashes($_GET['groupid']));
                $date = htmlspecialchars(addslashes($_GET['date']));
                $startDate = (new DateTime($date))->setTimezone(new DateTimeZone('Europe/Moscow'))->getTimestamp();
                $endDate = $startDate + 86400;

                $vkService = new VkService($access_token, $group_id);

                /**
                 *
                 * Get conversations
                 *
                 */

                $dialogs = $vkService->getConversations();
                if(is_array($dialogs)) {
                    $opponents = Helper::getOpponents($dialogs, $startDate);
                } else {
                    echo $dialogs.'<br>';
                }


                /**
                 *
                 * Analyse messages
                 *
                 */


                $results = [];

                if (is_array($opponents)) {
                    foreach($opponents as $opp) {
                        $history = $vkService->getMessagesByConversation($opp);
                        $res = Helper::getTimings($history, $startDate, $endDate, $group_id);
                        $results = array_merge($results, $res);
                    }
                } else {
                    echo $opponents.'<br>';
                }

                if (is_array($results)) {
                    $cookedData = Helper::processResults($results);
                }

                if ($cookedData) {
                    echo "<br>
                        <div class='requesteddate col-sm-12'>
                            Requested date: <strong>".$date."</strong>
                        </div><br>
                        <div class='average col-sm-12'>
                            Average answer time: <strong>".Helper::getAverage($cookedData['all'])." minuts</strong>
                        </div><br>
                        <div class='longanswers col-md-12'><strong>Long answers</strong></div>
                        <table border='1' class='table'>
                        <head><th>Delay</th><th>Dialog with</th></head>
                        ";
                    foreach($cookedData['more15'] as $long) {
                        echo "<tr>
                                <td>".$long['delay']." minuts</td>
                                <td>".$long['question_author']."</td>
                            </tr>";
                    }

                    echo "</table>";
                    echo "<div class='rathervalues col-md-12'><strong>Rather answers</strong></div>
                        <table border='1' class='table'>
                        <head><th>Delay</th><th>Entries</th></head>
                    ";
                    $rather = Helper::getRatherValues($cookedData['all']);
                    $count = 0;
                    foreach($rather as $key => $value ) {
                        echo "<tr>
                                <td>".($key/10)." minuts</td>
                                <td>".$value."</td>
                            </tr>";
                        if ($count == 10 | $count>= count($rather)) {
                            break;
                        }
                    }

                    echo "</table>";
                } else {
                    echo "<div class='well'>No data</div>";
                }

            }
        ?>
    </div>
    </div>
</div>
</body>
</html>
