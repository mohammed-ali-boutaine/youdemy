<?php
require_once('Connection.php');

class Tag{
    private $id;
    private $titre;

    public function __construct($id=null,$titre=null){
        $this->id=$id;
        $this->titre=$titre;
    }
    public function getId(){
        return $this->id;
    } 
    public function setId($id){
        $this->id=$id;
    }
    public function getTitre(){
        return $this->titre;
    }
    public function setTitre($titre){
        $this->titre=$titre;
    }



    public function add(){
        $pdo=Database::getInstance()->getConnection();
        $stm=$pdo->prepare("INSERT into tag (titre) value(:titre)");
        $stm->bindParam(':titre',$this->titre,PDO::PARAM_STR);
        $resultat=$stm->execute();
        if ($resultat) {
            $this->id=$pdo->lastInsertId();
            return true ;
        } else {
            return false ;
        }
        
    }
    public static function afficherTags(){
        $pdo=Database::getInstance()->getConnection();
        $stm=$pdo->prepare("SELECT * from tag");
        $stm->execute();
        $resultat=$stm->fetchAll(PDO::FETCH_ASSOC);
        $data=[];
        
        if($resultat) {
            foreach ($resultat as $value) {
                $tag=new Tag($value['id'],$value['titre']);
                $data[]=$tag;
            }
            return $data ;
        }
    }
}

?>