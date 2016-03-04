# pager_for_ci
A pager library for CodeIgniter, 2 and 3 all compatible.

###### application/core/My_Controller.php

```php
class MY_Controller extends CI_Controller
{
    public $rowSize = 20;
    public $pageLimit = 10;
    public $pagerParams = array();

    public function __construct()
    {
        parent::__construct();
        $this->pagerParams = [
            'page' => (int) $this->input->get('page'),
            'rowSize' => $this->rowSize,
            'pageLimit' => $this->pageLimit,
        ];
    }
}
```

###### application/controllers/Welcome.php

```php
class Welcome extends MY_Controller
{
    public function index()
    {
        $this->pagerParams['rowSize'] = 3;
        $this->load->library('pager');
        $this->load->helper('url');
        $this->load->model('Members', 'members', true);
        $datas = $this->members->datas();
        $data['datas'] = $datas;
        $this->load->view('pager', $data);
    }
}
```

###### application/models/Members.php

```php
class Members extends MY_Model
{
    protected $table = 'members';

    public function datas()
    {
        $this->db->where('id >', 1);

        return $this->pager->pager_init($this->table, $this->db)->result_array();
    }
}
```

If you want to do your own select here is the sample

```php
class Members extends MY_Model
{
    protected $table = 'members';

    public function datas()
    {
        // Select straightly
        $select = 'name AS the_name, column1, column2';
        
        // Select with MySQL function
        $select = [
            'name' => true,
            'CONCAT(`column1`, `column2`) AS `new_column`' => false,
            'SUM(`column`) AS `total`' => false
        ];

        return $this->pager->pager_init($this->table, $this->db, $select)->result_array();
    }
}

```

###### application/views/pager.php

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pager</title>
</head>
<body>
<h3>Pager</h3>
<?php foreach ($datas as $data): ?>
<div><?php echo $data->id; ?> <?php echo $data->name; ?></div>
<?php endforeach ?>
total: <?php echo $this->pager->totalRows; ?>
<ul>
<?php echo $this->pager->pagerString; ?>
</ul>
</body>
</html>
```
