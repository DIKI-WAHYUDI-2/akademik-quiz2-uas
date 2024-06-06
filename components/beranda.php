<?php
class BerandaController {
    private $model, $view;
    public function __construct() {
        $this->model = new BerandaModel();
        $this->view = new BerandaView();
    }
    public function index() {
        $this->view->index();
    }
}
class BerandaModel {

}
class BerandaView {
    public function index() {
?>
    <div class="pmd-card pmd-z-depth">      
        <div class="pmd-card-title">
            <h2 class="pmd-card-title-text typo-fill-secondary">Dashboard</h2>
        </div>
        <div class="pmd-card-body">
            
        </div>
    </div>
<?php
    }
}
?>