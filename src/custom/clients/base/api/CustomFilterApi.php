<?php

// Enrico Simonetti
// enricosimonetti.com

// 2018-06-07 on 8.0.0 with MySQL

use Doctrine\DBAL\Connection;

class CustomFilterApi extends FilterApi
{
    public function registerApiRest()
    {
        return parent::registerApiRest();
    }

    protected static function addFilter($field, $filter, SugarQuery_Builder_Where $where, SugarQuery $q)
    {
        if ($field == 'tag' && !empty($filter['$and_in']) && is_array($filter['$and_in'])) {

            $module_name = $q->getFromBean()->module_name;
            $main_table = $q->getFromAlias();

            $tags_lowercase = array_map('strtolower', $filter['$and_in']);

            $qb = DBManagerFactory::getInstance()->getConnection()->createQueryBuilder();
            $qb->select('tbr.bean_id')
                ->from('tags', 't')
                ->leftJoin('t', 'tag_bean_rel', 'tbr', 't.id = tbr.tag_id')
                ->where('t.deleted = ' . $qb->createPositionalParameter(0))
                ->andWhere('tbr.deleted = ' . $qb->createPositionalParameter(0))
                ->andWhere('tbr.bean_module = ' . $qb->createPositionalParameter($module_name))
                ->andWhere($qb->expr()->in(
                    't.name_lower',
                    $qb->createPositionalParameter((array) $tags_lowercase, Connection::PARAM_STR_ARRAY)
                ))
                ->groupBy('tbr.bean_id')
                ->having('count(distinct(t.id)) = ' . $qb->createPositionalParameter(count($filter['$and_in'])));

            $tag_alias = 'multi_tag_sel';
            $q->joinTable(
                $qb,
                array(
                    'alias' => $tag_alias,
                )
            )->on()->equalsField($tag_alias . '.bean_id', $main_table . '.id');
        } else {
            parent::addFilter($field, $filter, $where, $q);
        }
    }
}
