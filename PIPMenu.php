<?php 
/**
 * 
 * PIPMenu
 * @author Andrei Coelho
 * @version 1.0.0
 * 
 * GIT HUB: https://github.com/andrei-coelho/PIPMenu
 * License: MIT License - https://github.com/andrei-coelho/PIPMenu/blob/master/LICENSE
 * 
 * Esta pequena classe gera um menu nav bootstrap usando as 
 * configurações do painel wp-admin
 * 
 * enjoy =)
 * 
 * 
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
         * array auxiliar na construção dos pais
         *
         * @var array
         */
        private $fathers = array();

        /**
         * array que contem os objetos WPNavItem
         *
         * @var array
         */
        private $itens   = array();

        /**
         * tipo de menu (dropdown)
         *
         * @var string
         */
        private $type  = 'dropdown';

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
                    $this->fathers[] = new WPNavItem($itemMenu);
                }
            }

            $this->itens = $this->generateMenu($this->fathers, $this->childs);

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

        public function setType(string $type){
            $this->type = $type;
            return $this;
        }

        /**
         * retorna um array de objetos WPNavItem
         * 
         * @return array 
         */
        public function getItens(){
            return $this->itens;
        }

        /**
         * Uma função auxiliar
         * gera um menu em bootstrap
         * 
         * @return void 
         */
        public function genNav(){
            
            foreach ($this->itens as $item) {

                $child = $item->hasChild();
                $link = $child ? "#" : $item->url;

                echo '<li class="nav-item '.$this->type.'">' .
                     '<a class="nav-link dropdown-toggle" href="'.$link.'" id="navbarDropdown'.$item->ID.'" 
                      role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        '.$item->title.'
                      </a>';

                if($child){
                    $this->genChildsNav($item->getChilds());
                }

                echo '</li>';

            }

        }

        /**
         * Uma função auxiliar da genNav
         * para gerar os submenus
         * 
         * @return void 
         */
        private function genChildsNav($childs){

            echo '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';

            foreach ($childs as $child) {

                if($child->hasChild()){

                    echo '<div class="'.$this->type.'">
                    <a href="#" class="dropdown-item dropdown-toggle" id="dropdownMenuButton'.$child->ID.'" pip-event="hover" data-toggle="dropdown">
                        '.$child->title.'
                    </a>';

                    $this->genChildsNav($child->getChilds());
                    echo '</div>';

                } else {
                    echo '<a class="dropdown-item" href="'.$child->url.'">'.$child->title.'</a>';
                }

            }
            echo '</div>';
        }

        /**
         * popula o array $itens com objetos WPNavItem
         * 
         * @return void 
         */
        private function generateMenu(array $pais){
            
            $listPais = array();

            foreach ($pais as $pai) {
               
                if(isset($this->childs[$pai->ID])){
                   
                    $meusFilhos = $this->childs[$pai->ID];
                    $meuFilhosENetos = $this->generateMenu($meusFilhos);
                    $pai->setChildren($meuFilhosENetos);

                }

                $listPais[] = $pai;

            }

            return $listPais;

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
        
        public  $ID, $title, $url;
        private $childs = array();

        function __construct(WP_Post $post){

            $this-> ID    = $post -> ID;
            $this-> title = $post -> title;
            $this-> url   = $post -> url;

        }

        public function hasChild(){
            return count($this->childs) > 0;
        }

        public function setChildren(array $list){
            $this->childs = $list;
        }

        public function getChilds(){
            return $this->childs;
        }

        public function getChild(int $id){
            return $this->childs[$id];
        }

    }


} // END IF