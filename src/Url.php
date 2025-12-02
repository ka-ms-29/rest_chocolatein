<?php


require '../vendor/autoload.php';
use Dotenv\Dotenv;
/**
 * Description of Url
 *
 * @author Mostaghfera Jan
 */
class Url {
    
    /**
     * instance de la classe actuelle
     * @var type
     */
    private static $instance = null;
    
    /**
     * tableau contenant toutes les variables transmises
     * @var type array 
     */
    private $data = [];
    /**
     * constructeur privé
     * récupère les variables d'environnement
     * récupère les variables envoyées via l'url
     */
    private function __construct() {
        // variables d'environnement
        $this->dotenv = Dotenv::createImmutable(__DIR__);
        $this->dotenv->load();
        // variables envoyées par l'url
        $this->data = $this->recupAllData();
    }
    
    /**
     * méthode statique de création de l'instance unique
     * @return Url
     */
    public static function getInstance() : Url{
        if(self::$instance === null){
            self::$instance = new Url();
        }
        return self::$instance;
    }
    
    /**
     * récupération de toutes les variables envoyées par l'URL
     * nettoyage et retour dans un tableau associatif
     * @return array
     */
    private function recupAllData() : array{
        $data = [];
        if(!empty($_GET)){
            $data = array_merge($data, $_GET);
        }
        if(!empty($_POST)){
            $data = array_merge($data, $_POST);
        }
        $input = file_get_contents('php://input');
        parse_str($input, $postData);
        $data = array_merge($data, $postData);    
        // htmlspecialchars appliqué à chaque valeur du tableau
        $data = array_map(function($value) {
            return htmlspecialchars($value, ENT_NOQUOTES);
        }, $data);
        return $data;
    }
    
    /**
     * retour d'une variable avec les caractères spéciaux convertis
     * et au format array si format "json" reçu
     * possibilité d'ajouter d'autres 'case' de conversions
     * @param string $nom
     * @param string $format
     * @return string|array|null
     */
    public function recupVariable(string $nom, string $format="string") : string|array|null{
        $variable = $this->data[$nom] ?? '';
        switch ($format){
            case "json" : 
                $variable = $variable ? json_decode($variable, true) : null;
                break;
        }
        return $variable;
    }
    
    /**
     * récupère la méthode HTTP utilisée pour le transfert
     * @return string
     */
    public function recupMethodeHTTP() : string{
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * compare le user/pwd reçu en 'basic auth' 
     * avec le user/pwd dans les variables d'environnement
     * @return bool true si l'authentification reussi
     */
    private function basicAuthentification() : bool{
        // récupère les variables d'environnement de l'authentification
        $expectedUser = htmlspecialchars($_ENV['AUTH_USER'] ?? '');
        $expectedPw = htmlspecialchars($_ENV['AUTH_PW'] ?? '');  
        // récupère les variables envoyées en 'basic auth'
        $authUser = htmlspecialchars($_SERVER['PHP_AUTH_USER'] ?? '');
        $authPw = htmlspecialchars($_SERVER['PHP_AUTH_PW'] ?? '');    
        // Contrôle si les valeurs d'authentification sont identiques
        return ($authUser === $expectedUser && $authPw === $expectedPw) ;
    }
    
    /**
     * vérifie l'authentification suivant la demande
     * possibilité d'ajouter des 'case' et de nouvelles fonctions 
     * si besoin d'un autre type d'authentification 
     * @return bool
     */
    public function authentification(): bool{
        $authentification = htmlspecialchars($_ENV['AUTHENTIFICATION'] ?? '');
        switch ($authentification){
            case '' : return true ;
            case 'basic' : return self::basicAuthentification() ;
            default : return true;
        }
    }

}
