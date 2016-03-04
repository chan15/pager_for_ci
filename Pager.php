<?php

class Pager
{
    public $page = 0;
    public $totalRows = 0;
    public $totalPages = 0;
    public $actualPages = 0;
    public $langPrevPage = 'prev page';
    public $langFirstPage = 'first page';
    public $langNextPage = 'next page';
    public $langLastPage = 'last page';
    public $pagerString = '';
    public $pagerBootstrpString = '';

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function pager_init($table, $query = null, $select = null)
    {
        $pagerParams = $this->ci->pagerParams;
        $query->select('SQL_CALC_FOUND_ROWS *', false);

        if ($select !== null) {
            if (is_array($select)) {
                foreach ($select as $key => $value) {
                    $query->select($key, $value);
                }
            } else {
                $query->select($select);
            }
        }

        $query->limit($pagerParams['rowSize'], $pagerParams['page'] * $pagerParams['rowSize']);
        $rows = $query->get($table);

        $query = $query->query('SELECT FOUND_ROWS() AS `count`');
        $totalRows = $query->row()->count;
        $pagerParams['totalRows'] = $totalRows;
        $this->bootstrapPager($pagerParams);
        $this->pager($pagerParams);

        return $rows;
    }

    public function bootstrapPager($params)
    {
        extract($params);
        $this->totalRows = $totalRows;
        $currentPage = current_url();
        $totalPages = ceil($totalRows / $rowSize) - 1;
        $limitLinksEndCount = $pageLimit;
        $temp = intval(($page + 1));
        $startLink = intval(max(1, $temp - intval($limitLinksEndCount / 2)));
        $temp = intval($startLink + $limitLinksEndCount - 1);
        $endLink = min($temp, $totalPages + 1);
        $pager = '<ul class="pagination">';

        // Prev page
        if ($page > 0) {
            $pager .= sprintf('<li><a href="%s?page=%d%s">«</a></li>',
                $currentPage,
                max(0, intval($page - 1)),
                $this->combineQueryString('page'));
        } else {
            $pager .= sprintf('<li class="disabled"><a>«</a></li>',
                $currentPage,
                max(0, intval($page - 1)),
                $this->combineQueryString('page'));
        }

        if ($endLink !== $temp) {
            $startLink = max(1, intval($endLink - $limitLinksEndCount + 1));
        }

        for ($i = $startLink; $i <= $endLink; ++$i) {
            $limitPageEndCount = $i - 1;

            if ($page !== $limitPageEndCount) {
                $pager .= sprintf('<li><a href="%s?page=%d%s">%s</a></li>',
                    $currentPage,
                    $limitPageEndCount,
                    $this->combineQueryString('page'),
                    $i);
            } else {
                $pager .= '<li class="disabled"><a>'.$i.'</a></li>';
            }
        }

        // Next page
        if ($page < $totalPages) {
            $pager .= sprintf('<li><a href="%s?page=%d%s">»</a></li>',
                $currentPage,
                min($totalPages, intval($page + 1)),
                $this->combineQueryString('page'));
        } else {
            $pager .= sprintf('<li class="disabled"><a>»</a></li>',
                $currentPage,
                min($totalPages, intval($page + 1)),
                $this->combineQueryString('page'));
        }

        $pager .= '</ul>';

        $this->pagerBootstrpString = $pager;
    }

    /**
     * Default pager.
     *
     * @param int $page
     * @param int $rowSize
     * @param int $totalRows
     * @param int $limit     data per page
     *
     * @return string
     */
    public function pager($params)
    {
        extract($params);

        if ($rowSize > $totalRows) {
            return '';
        }

        $this->page = $page;
        $this->totalPages = ceil($totalRows / $rowSize) - 1;
        $sep = '';
        $result = '';
        $result .= $this->pageString('first', 'First', 'first_select icon-arrowend').$sep;
        $result .= $this->pageString('prev', 'Prev', 'pre_select icon-arrow').$sep;
        $result .= $this->pageNumber($pageLimit).$sep;
        $result .= $this->pageString('next', 'Next', 'next_select icon-arrow').$sep;
        $result .= $this->pageString('last', 'Last', 'last_select icon-arrowend').$sep;

        $this->pagerString = $result;
    }

    /**
     * Prev or nex page.
     *
     * @param string $method prev or next
     * @param string $string display word
     * @param string $class  css class name
     *
     * @return string
     */
    public function pageString($method, $string = null, $class = '')
    {
        $currentPage = current_url();
        $result = '';

        switch ($method) {
            case 'first':
                if ($this->page > 0) {
                    if ($string === null) {
                        $string = $this->langFirstPage;
                    }

                    $result = '<li><a href="'.sprintf('%s?page=%d%s',
                        $currentPage,
                        0,
                        $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a></li>';
                }

                break;
            case 'prev':
                if ($this->page > 0) {
                    if ($string === null) {
                        $string = $this->langPrevPage;
                    }

                    $result = '<li><a href="'.sprintf('%s?page=%d%s',
                        $currentPage,
                        max(0, $this->page - 1),
                        $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</li></a>';
                }

                break;
            case 'next':
                if ($this->page < $this->totalPages) {
                    if ($string === null) {
                        $string = $this->langNextPage;
                    }

                    $result = '<li><a href="'.sprintf('%s?page=%d%s',
                        $currentPage,
                        min($this->totalPages, $this->page + 1),
                        $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</li></a>';
                }

                break;
            case 'last':
                if ($this->page < $this->totalPages) {
                    if ($string === null) {
                        $string = $this->langLastPage;
                    }

                    $result = '<li><a href="'.sprintf('%s?page=%d%s',
                        $currentPage,
                        $this->totalPages,
                        $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a></li>';
                }

                break;
        }

        return $result;
    }

    /**
     * Combine url param.
     *
     * @param string $string combine string
     *
     * @return string
     */
    public function combineQueryString($string)
    {
        $result = '';

        if (empty($_SERVER['QUERY_STRING']) === false) {
            $params = explode('&', $_SERVER['QUERY_STRING']);
            $newParams = array();

            foreach ($params as $param) {
                if (stristr($param, $string) === false) {
                    array_push($newParams, $param);
                }
            }

            if (count($newParams) !== 0) {
                $result = '&'.htmlentities(implode('&', $newParams));
            }
        }

        return $result;
    }

    /**
     * Page number.
     *
     * @param int    $limit data per page
     * @param string $set   seperation
     *
     * @return string
     */
    public function pageNumber($limit = 5, $sep = '&nbsp;')
    {
        $result = '';
        $currentPage = current_url();
        $limitLinksEndCount = $limit;
        $temp = intval($this->page + 1);
        $startLink = max(1, $temp - intval($limitLinksEndCount / 2));
        $temp = intval($startLink + $limitLinksEndCount - 1);
        $endLink = min($temp, $this->totalPages + 1);

        if ($endLink !== $temp) {
            $startLink = max(1, $endLink - $limitLinksEndCount + 1);
        }

        for ($i = $startLink; $i <= $endLink; ++$i) {
            $limitPageEndCount = intval($i - 1);

            if ($limitPageEndCount !== $this->page) {
                $result .= sprintf('<li><a href="'.'%s?page=%d%s', $currentPage, $limitPageEndCount, $this->combineQueryString('page').'">');
                $result .= $i.'</a></li>';
            } else {
                $result .= '<li><strong>'.$i.'</strong></li>';
            }

            if ($i !== $endLink) {
                $result .= $sep;
            }
        }

        return $result;
    }
}
