<?
class Helper
{

    public static function checkMsgDate(array $msg, int $startday, int $endDay)
    {

        if ($msg['date'] >= $startday && $msg['date'] <= $endDay){
            return true;
        } else {
            return false;
        }

    }


    public static function getOpponents(array $dialogs, int $startDate)
    {

        $opponents = [];
        foreach($dialogs['items'] as $dialog) {

            if($dialog['last_message']['date']>$startDate) {
                $opponents[] = $dialog['conversation']['peer']['id'];
            }

        }

        return $opponents;

    }

    public static function getTimings(array $history, int $startDate, int $endDate, int $groupId)
    {

        $messages = Helper::getMessages($history, $startDate, $endDate);
        return Helper::searchAnswers($messages, $groupId);

    }

    public static function getMessages(array $history, int $startDate, int $endDate)
    {

        $messages = [];

        foreach($history['items'] as $msg) {
            if(Helper::checkMsgDate($msg, $startDate, $endDate)) {
                $messages[] = $msg;
            }
        }

        return $messages;
    }

    private static function searchAnswers(array $messages, int $groupId)
    {

        $answers = [];
        for ($i=0; $i<count($messages); $i++) {
            if (Helper::isAnswer($messages[$i], $groupId) && Helper::isQuestion($messages[$i+1], $groupId)) {

                $answers[] = [
                    'delay' => $messages[$i]['date'] - $messages[$i+1]['date'],
                    'answer' => $messages[$i],
                    'question' => $messages[$i+1]
                ];

            }

        }

        return $answers;
    }

    public static function isAnswer(array $msg, int $groupId)
    {
        if ($msg['from_id'] == (0 - $groupId)) {
            return true;
        } else {
            return false;
        }
    }

    private static function isQuestion(array $msg, int $groupId)
    {
        if ($msg['from_id'] != $groupId) {
            return true;
        } else {
            return false;
        }
    }

    public static function processResults($results)
    {

        $cooked = [];

        foreach($results as $result) {

            $time = (int)$result['delay']/60;
            if ($time > 15) {
               $cooked['more15'][] = [
                   'delay' => $time,
                   'question_author' => $result['question']['from_id'],
               ];
            }
            $cooked['all'][] = (int)($time*10);
        }

        return $cooked;

    }

    public static function getAverage(array $all)
    {
        return array_sum($all)/(count($all)*10);
    }

    public static function getRatherValues(array $all)
    {
        $countedVals = array_count_values($all);
        arsort($countedVals);
        return $countedVals;
    }

}