<?php
class Foo
{
    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }
}
class Admin_parent extends User_Parent {
	public $currentPlayer="oponent";
	public $testFunction;
	public function __construct(){
		parent::__construct();
	}
	public function test(){
		/*$player="oponent";
		$str="dealDamage(\$this,10);;";
		function dealDamage($object,$damage){
			echo "You did ".$damage." damage to ".$object->currentPlayer;
		}
		eval($str);*/
		/*function tester(){
			return function(){
				echo "bla";
			};
		}
		$test=tester();
		$test();
		*/
		$that=$this;
		$test = function() use ($that){
			echo $that->currentPlayer;
		};
		$test();
	}
	
}
class RP_Parent extends User_Parent {
	public $userId;
	public function __construct() {
		parent::__construct();
		$this->userId=parent::getIdForced();
	}
}

class User_Parent extends CI_Controller {
	//used to make sure the construct of the parent always gets executed
	public $sessionData;
	public function __construct() {
		parent::__construct();
		$this->sessionData=$this->session->get_userdata();
		if(!isset($this->sessionData['noForge'])){
			$this->load->helper("string");
			$this->sessionData['noForge']=random_string("alnum",8);
			$this->session->set_userdata(array("noForge"=>$this->sessionData['noForge']));
		}
	}
	//check if the request really comes from the user and not something else
	public function checkLegit($code,$mode="error",$to="profile"){
		if($code != $this->sessionData['noForge']){
			if($mode=="redirect") {
				redirect($to);
			}elseif($mode=="die"|| $mode=="exit"){
				exit;
			} else {
				return array("success"=>false,"error"=>"Strings don't match'");
			}
		}
		return array("success"=>true);
	}
	//automatically cleans the input
	public function getPostSafe($alsoGiveError=false){
		//$this->load->library("security");
		if($alsoGiveError){
			$text=$this->input->post();
			$clean=$this->security->xss_clean($this->input->post());
			$safe=false;
			if($clean===$text){
				$safe=true;
			}
			return array("safe"=>$safe,"clean"=>$clean,"raw"=>$text);
		} else {
			return $this->security->xss_clean($this->input->post());
		}
	}
	//used to force a login
	public function forceLogIn(){
		if(!$this->session->has_userdata("userId")){
			redirect("login");
		}
	}
	//used to force a log in and get the user id as well
	public function getIdForced(){
		$this->forceLogIn();
		return $this->session->userId;
	}
	public function redirectLoggedIn(){
		if($this->session->has_userdata("userId")){
			redirect("profile");
		}
	}
	//only loads the header
	public function loadHeader($dataOverWrite=false,$withCard=false){
		//$this->load->model("defaults_model.php");
		$headerData=array();
		if($dataOverWrite){
			$headerData=$dataOverWrite;
			
		} else {
			if($this->session->has_userdata("userId")){
				$headerData['loggedIn']=true;
			}else{
				$headerData['loggedIn']=false;
			}
		}
		$this->load->view("front/defaults/header.php",$headerData);
		$this->load->view("front/defaults/card.php");
	}
	//loads the header, the sidebars, the specified view and the footer
	public function loadAll($view,$data=array(),$overWriteHeader=false,$withCard=false){
		$this->loadHeader($overWriteHeader,$withCard);
		$this->load->view("front/defaults/firstSideBar");
		$this->load->view("front/".$view,$data);
		$this->load->view("front/defaults/secondSideBar");
		$this->load->view("front/defaults/footer.php");
	}
	//used to load the default header+a normal view+footer
	public function loadbasics($view,$data=array(),$withCard=false){
		$this->loadHeader(false,$withCard);
		$this->load->view("front/".$view,$data);
		$this->load->view("front/defaults/footer.php");
		
	}

}
