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

###### application/core/MY_Model.php

```php
class MY_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function pager_init($item)
    {
        $pagerParams = $this->pagerParams;
        $item->select('SQL_CALC_FOUND_ROWS *', false);
        $item->limit($pagerParams['rowSize'], $pagerParams['page'] * $pagerParams['rowSize']);
        $rows = $item->get($this->table);

        $query = $item->query('SELECT FOUND_ROWS() AS `count`');
        $totalRows = $query->row()->count;
        $pagerParams['totalRows'] = $totalRows;
        $this->pager->init($pagerParams);

        return $rows->result();
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

        return $this->pager_init($this->db);
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
