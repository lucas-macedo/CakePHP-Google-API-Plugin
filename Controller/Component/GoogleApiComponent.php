<?php
/**
 * Google Api Plugin
 *
 * @author Lucas Macedo
 * @package Google Api
 * @license http://opensource.org/licenses/GPL-3.0 GPL V3 License
 */

Configure::load('GoogleApi.core');
class GoogleApiComponent extends Component {
    public $controller = null;

    public $Client = array();

    private $_default = array();

    public function __construct($collection, $settings = array()) {
    
    // inclue api google e seus serviços 
    include APP . 'Plugin' . DS . 'GoogleApi' . DS . 'Vendor' . DS . 'GoogleApi' . DS . 'src' . DS . 'Google' . DS . 'autoload.php';
    
    // seta as informações necessárias
    $this->settings = array_merge($this->_default, Configure::read('GoogleApi') , $settings);
    
    // define o client, ele é necessário pra todas aplicações do google (Analytics, Driver, etc)
    $this->Client = new Google_Client();
    $this->Client->setApplicationName(Configure::read('GoogleApi.client.ApplicationName'));
  }

  /**
   * Inicia a  biblioteca do Google Analytics
   * @return object
   */

   public function Analytics() {

      $analytics = new Google_Service_Analytics($this->Client);

      // Lê a chave gerada client_secrets.p12 e seta as configurações necessárias (Google Analytics)
      $key = file_get_contents(Configure::read('GoogleApi.client.key_file_location'));
      $cred = new Google_Auth_AssertionCredentials(
          Configure::read('GoogleApi.client.email_address'),
          array(Google_Service_Analytics::ANALYTICS_READONLY),
          $key
      );

      // Verifica a chave e retorna a autenticação
      $this->Client->setAssertionCredentials($cred);
      if($this->Client->getAuth()->isAccessTokenExpired()) {
        $this->Client->getAuth()->refreshTokenWithAssertion($cred);
      }

      return $analytics;
    }
  /**
   * Obtem o primeiro profile_id do google analytics (caso saiba nao é preciso utilizar a função)
   * @param object $analytics objeto de inicialização & autenticação do google analytics
   * @return object
   */
   public function getFirstprofileAnalyticsId($analytics) {
      // Get the user's first view (profile) ID.

      // Get the list of accounts for the authorized user.
      $accounts = $analytics->management_accounts->listManagementAccounts();

      if (count($accounts->getItems()) > 0) {
        $items = $accounts->getItems();
        $firstAccountId = $items[0]->getId();

        // Get the list of properties for the authorized user.
        $properties = $analytics->management_webproperties
            ->listManagementWebproperties($firstAccountId);

        if (count($properties->getItems()) > 0) {
          $items = $properties->getItems();
          $firstPropertyId = $items[0]->getId();

          // Get the list of views (profiles) for the authorized user.
          $profiles = $analytics->management_profiles
              ->listManagementProfiles($firstAccountId, $firstPropertyId);

          if (count($profiles->getItems()) > 0) {
            $items = $profiles->getItems();

            // Return the first view (profile) ID.
            return $items[0]->getId();

          } else {
            throw new Exception('No views (profiles) found for this user.');
          }
        } else {
          throw new Exception('No properties found for this user.');
        }
      } else {
        throw new Exception('No accounts found for this user.');
      }
    }

  /**
   * Obtem nos ultimos 12 messes PageViews, Visitas(Sessions), Duração média da Sessão
   * @param object $analytics objeto de inicialização & autenticação do google analytics
   * @param object $profileId id do perfil google analytics
   * @return object
   */

    public function getMonthsVisits($analytics, $profileId) {

          $start_date = date('Y-m-01', strtotime("-12 month")); 
          $end_date = date('Y-m-t', strtotime("-0 month"));

          $params = array(
          'dimensions' => 'ga:month',
          );

          // requesting the data  
          $results = $analytics->data_ga->get(
            "ga:{$profileId}", $start_date, $end_date, "ga:pageviews,  ga:sessions, ga:sessionDuration",$params);  
          $rows = $results->getRows(); 

          // os retorno da coluna row são sempre na ordem "ga:pageviews,  ga:sessions, ga:sessionDuration"

          //return $rows;
          return $results;
    }

    /**
     * Obtem as páginas mais visitadas no site (endereço,qtd de PageViews e Título da Página)
     * @param object $analytics objeto de inicialização & autenticação do google analytics
     * @param object $profileId id do perfil google analytics
     * @return object
     */
    public function getPagesVisiteds($analytics, $profileId) {

          $start_date = date('Y-m-01', strtotime("-12 month")); 
          $end_date = date('Y-m-t', strtotime("-0 month"));

          //$params = array('dimensions' => 'ga:pagePath, ga:pageTitle'); 
          $params = array(
          'dimensions' => 'ga:PagePath, ga:pageTitle',
          'sort' => '-ga:pageviews',
          'metrics' => 'ga:pageviews',
          'max-results' => '10');

          // requesting the data  
          $results = $analytics->data_ga->get(
            "ga:{$profileId}", $start_date, $end_date, "ga:pageviews",$params);  
          $rows = $results->getRows(); 
              


          //return $rows;
          return $results;
    }


}