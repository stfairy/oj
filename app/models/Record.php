<?php
class Record extends fActiveRecord
{
  protected function configure()
  {
    //
  }
  
  public static function find($top, $owner, $problem_id, $language, $verdict)
  {
    $conditions = array();
    if (!empty($top)) {
      $conditions['id<='] = $top;
    }
    if (strlen($owner)) {
      $conditions['owner='] = $owner;
    }
    if (strlen($problem_id)) {
      $conditions['problem_id='] = $problem_id;
    }
    if (!empty($language)) {
      $conditions['code_language='] = $language - 1;
    }
    if (!empty($verdict)) {
      $conditions['verdict='] = $verdict;
    }
		$limit = Variable::getInteger('records-per-page', 50);
		return fRecordSet::build('Record', $conditions, array('id' => 'desc'), $limit);
  }
  
  public function getLanguageName()
  {
    return SubmitController::$languages[$this->getCodeLanguage()];
  }
  
  private static $regexPattern = "/\\w+\\s\\(Time:\\s(?P<time>\\d+)ms,\\sMemory:\\s(?P<memory>\\d+)kb\\)/";
  
  public function getTimeCost()
  {
    if (preg_match_all(self::$regexPattern, $this->getJudgeMessage(), $matches)) {
      return array_sum($matches['time']) . 'ms';
    }
    return 'N/A';
  }
  
  public function getMemoryCost()
  {
    if (preg_match_all(self::$regexPattern, $this->getJudgeMessage(), $matches)) {
      return array_sum($matches['memory']) . 'kb';
    }
    return 'N/A';
  }
  
  public function getResult()
  {
    if ($this->getJudgeStatus() == JudgeStatus::DONE) {
      return Verdict::$NAMES[$this->getVerdict()];
    }
    return JudgeStatus::$NAMES[$this->getJudgeStatus()];
  }
  
  public function isReadable()
  {
    return fAuthorization::getUserToken() == $this->getOwner() or User::can('view-any-record');
  }
}
