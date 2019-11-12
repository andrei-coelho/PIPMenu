<?php 
/**
 * WP Nav Menu
 */


if ( ! class_exists( 'PIPMenu' ) and ! class_exists( 'WPNavItem' ) ) {
    
    /**
     * 
     * PIPMenu
     * Classe que armazena e gera menus
     * 
     */
    class PIPMenu {

        /**
         * variável que armazena um array com as localizações dos menus
         *
         * @var array
         */
        private static $theme_locations  = null;
        
        /**
         * array com os objetos PIPMenu
         *
         * @var array
         */
        private static $list_menus = array();
        
        /**
         * array auxiliar na construção dos filhos
         *
         * @var array
         */
        private $childs  = array();

        /**
         * array que contem os objetos WPNavItem
         *
         * @var array
         */
        private $parents = array();

        /**
         * construtor
         *
         * @param string $location
         */
        private function __construct(string $location){

            if(self::$theme_locations == null){
                self::$theme_locations = get_nav_menu_locations();
            }

            $menu_obj = get_term( self::$theme_locations[$location], 'nav_menu' );

            foreach (wp_get_nav_menu_items( $menu_obj -> term_id) as $itemMenu) {
                if($itemMenu -> menu_item_parent > 0){
                    $this->childs[$itemMenu -> menu_item_parent][] =  new WPNavItem($itemMenu);
                } else {
                    $this->parents[] = new WPNavItem($itemMenu);
                }
            }

            $this->generateMenu($this->parents, $this->childs);

        }

        /**
         * tenta construir um menu usando a localização
         * se a localização não estiver registrada
         * o menu não será construído
         * 
         * @return void 
         */
        public static function setMenuByLocation($location){
            
            if(!isset(self::$list_menus[$location])){
                self::$list_menus[$location] = new PIPMenu($location);
            }
            
        }

        /**
         * retorna um objeto PIPMenu 
         * ou null caso a localização não exista
         * 
         * @return PIPMenu / null
         */
        public static function getMenu(string $location){

            self::setMenuByLocation($location);
            return self::$list_menus[$location];

        }

        /**
         * retorna um array de objetos WPNavItem
         * 
         * @return array 
         */
        public function getItens(){
            return $this->parents;
        }

        /**
         * Uma função auxiliar
         * gera um menu em bootstrap
         * 
         * @return void 
         */
        public function genBootstrapMenu(){
            
        }

        /**
         * popula o array $parents com objetos WPNavItem
         * 
         * @return void 
         */
        private function generateMenu(array &$lista, array &$filhos){

            foreach ($lista as $pai) {

                if(isset($filhos[$pai->ID])){

                    $meus = $filhos[$pai->ID];
                    $this->generateMenu($meus, $filhos);
                    $pai->setChildren($meus);
                    unset($filhos[$pai->ID]);

                }

            }

        }

    }

    /**
     * 
     * WPNavItem
     * Classe auxiliar na manipulação dos menus
     * QUASE uma inner class em php    :)
     * Essa classe se baseina no Objeto WP_Post do wordpres para sua construção
     * 
     */
    class WPNavItem {
        
        public $ID, $title, $url;
        private $child = array();

        function __construct(WP_Post $post){

            $this-> ID    = $post -> ID;
            $this-> title = $post -> title;
            $this-> url   = $post -> url;

        }

        public function hasChild(){
            return count($this->child) > 0;
        }

        public function setChildren(array $list){
            $this->child = $list;
        }

        public function getChilds(){
            return $this->child;
        }

        public function getChild(int $id){
            return $this->child[$id];
        }

    }


}