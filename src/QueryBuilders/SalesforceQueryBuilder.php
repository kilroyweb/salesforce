<?php

namespace KilroyWeb\Salesforce\QueryBuilders;

class SalesforceQueryBuilder extends BaseQueryBuilder
{

    protected $table;
    protected $type;
    protected $select;
    protected $filters;
    protected $columnValues;
    protected $limit = 100;

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($selectableColumns = [])
    {
        $this->type = 'select';
        $this->select = collect($selectableColumns);
        return $this;
    }

    public function where($filterableColumns = [])
    {
        $this->filters = collect($filterableColumns);
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
    }

    public function generate()
    {
        if ($this->type == 'select') {
            return $this->generateSelectSQL();
        }
        return false;
    }

    public function generateSelectSQL()
    {
        $sqlLines = [];
        $sqlLines[] = 'SELECT ' . implode(', ', $this->select->toArray());
        $sqlLines[] = 'FROM ' . $this->table;
        $whereLine = $this->generateWhereLine();
        if (!empty($whereLine)) {
            $sqlLines[] = $whereLine;
        }
        if ($this->limit != 0) {
            $sqlLines[] = 'LIMIT ' . $this->limit;
        }
        $sql = implode("\n", $sqlLines);
        return $sql;
    }

    public function generateWhereLine()
    {
        if ($this->filters->count() > 0) {
            $whereLines = [];
            foreach ($this->filters as $filterName => $filterValue) {
                $whereLines[] = $filterName . ' = \'' . $filterValue . '\'';
            }
            return 'WHERE ' . implode(' AND ', $whereLines);
        }
        return false;
    }

}
