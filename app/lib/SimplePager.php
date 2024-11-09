<style>
    .paginationBar {
        margin: 10px;
        padding: 10px;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin: 20px 0px 10px 0px;
    }

    .pagination .page {
        cursor: pointer;
        margin: 0 5px;
        padding: 5px 10px;
        border: 1px solid rgb(243, 243, 243);
        background-color: rgb(243, 243, 243);
        color: #111111;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .pagination .page:hover {
        background-color: #00ac72 !important;
        border-color: #bbb;
    }

    .pagination .page.active {
        font-weight: bold;
        background-color: rgb(105, 108, 255);
        color: white;
        border-color: rgb(105, 108, 255);
    }

    .pagination-nobg {
        width: fit-content;
        padding: 10px;
        background: none;
        box-shadow: 0 10px 16px 0 rgba(0, 0, 0, 0.2), 0 12px 40px 0 rgba(0, 0, 0, 0.19);
        border-radius: 20px;
    }
</style>

<?php

/**
 * Take From Practical 5
 */
class SimplePager
{
    public $limit;      // Page size
    public $page;       // Current page
    public $item_count; // Total item count
    public $page_count; // Total page count
    public $result;     // Result set (array of records)
    public $count;      // Item count on the current page

    public function __construct($query, $params, $limit, $page)
    {
        global $_db;

        // Set [limit] and [page]
        $this->limit = ctype_digit($limit) ? max($limit, 1) : 10;
        $this->page  = ctype_digit($page) ? max($page, 1) : 1;

        // Set [item count]
        $q   = preg_replace('/SELECT.+FROM/', 'SELECT COUNT(*) FROM', $query, 1);
        $stm = $_db->prepare($q);
        $stm->execute($params);
        $this->item_count = $stm->fetchColumn();

        // Set [page count]
        $this->page_count = ceil($this->item_count / $this->limit);

        // Calculate offset
        $offset = ($this->page - 1) * $this->limit;

        // Set [result]
        $stm = $_db->prepare($query . " LIMIT $offset, $this->limit");
        $stm->execute($params);
        $this->result = $stm->fetchAll();

        // Set [count]
        $this->count = count($this->result);
    }

    /**
     * Updated By: Chong Jun Xiang
     */
    public function html($href = '', $attr = '')
    {
        if (!$this->result) {
            return;
        }

        $prev = max($this->page - 1, 1);
        $next = min($this->page + 1, $this->page_count);


        echo "<div class='paginationBar'>";
        echo "<nav class='center pagination pagination-nobg' $attr>";
        echo "<a href='?page=1&$href' class='page'><<</a>";
        echo "<a href='?page=$prev&$href' class='page'><</a>";
        $start = max(1, $this->page - 2);
        $end   = min($this->page_count, $this->page + 2);
        for ($p = $start; $p <= $end; $p++) {
            $c = $p == $this->page ? 'active' : '';
            echo "<a href='?page=$p&$href' class='$c page'>$p</a>";
        }
        echo "<a href='?page=$next&$href' class='page'>></a>";
        echo "<a href='?page=$this->page_count&$href' class='page'>>></a>";
        echo "</nav>";
        echo "</div>";
    }
}
