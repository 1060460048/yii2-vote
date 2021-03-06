<?php
namespace shirase\vote\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\JsExpression;

class Vote extends Widget{
	protected $id;
	public $model;
	public $primaryField = 'id';
	public $viewPath = 'index';
	public $enableView = true;
	public $encode = true;
	public $voteModel = 'shirase\vote\models\Like';
	public $vote;
	public $modelField = 'model';
	public $modelIdField = 'model_id';
	public $typeField = 'type';
	public $userIdField = 'user_id';
	public $cancelable = false;
	public $guestErrorMessage;
	public $onGuest;
	public $actionPath;
	public $ajaxOptions=[];
	public $likeAction = 'like';
	public $dislikeAction = 'dislike';
	public $likeButton;
	public $dislikeButton;
	public $clientOptions = [];
	public $clientVar;

	public function init(){
		$this->id = self::getId(true);
		$this->likeButton = ($this->likeButton)?:'#'.$this->id.'-like';
		$this->dislikeButton = ($this->dislikeButton)?:'#'.$this->id.'-dislike';
	}
	public function clientWidget($var = null){
		$begin = '';
		if($var !== null){
			$begin = "var $var = ";
		}
		$class = get_class($this->model);
		$class = $this->encode?crc32($class):$class;
		$options = ArrayHelper::merge([
			'action_path'=>$this->actionPath,
			'ajax_options'=>$this->ajaxOptions,
			'like_button'=>$this->likeButton,
			'dislike_button'=>$this->dislikeButton,
			'cancelable'=>$this->cancelable,
			'model'=>$class,
			'id'=>$this->model[$this->primaryField],
		],$this->clientOptions);
		$options = Json::encode($options);
		$script = new JsExpression($begin."(new vote({$options}));");

		return $script;
	}
	public function run(){
		if(\Yii::$app->user->isGuest){
			if(is_callable($this->onGuest)){
				$run = $this->onGuest;
				$run();
			}
			return $this->guestErrorMessage;
		}
		VoteAsset::register($this->view);
		$this->view->registerJs($this->clientWidget($this->clientVar));
		if($this->enableView) {
			return $this->render($this->viewPath, [
				'model' => $this->model,
				'widgetId' => $this->id,
				'likeUrl' => Url::to($this->actionPath . $this->likeAction),
				'dislikeUrl' => Url::to($this->actionPath . $this->dislikeAction),
			]);
		}
	}
}