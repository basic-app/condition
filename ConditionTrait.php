<?php
/**
 * @author Basic App Dev Team <dev@basic-app.com>
 * @license MIT
 * @link https://basic-app.com
 */
namespace BasicApp\Condition;

trait ConditionTrait
{

    protected $orConditionDevider = ' | ';

    protected $andConditionDevider = ' & ';

    protected $emptyCondition = '/';

    protected $notEmptyCondition = '-/';

    protected $likeCondition = '%';

    protected $notCondition = '-';

    public function filterWhereCondition(string $field, string $condition)
    {
        if ($condition)
        {
            return $this->whereCondition($field, $condition);
        }

        return $this;
    }

    public function whereCondition(string $field, string $condition)
    {
        $andSegments = explode($this->andConditionDevider, $condition);

        $this->groupStart();

        foreach($andSegments as $andSegment)
        {
            if (!$andSegment)
            {
                continue;
            }

            $orSegments = explode($this->orConditionDevider, $condition);

            $andCondition = array_shift($orSegments);

            $this->applyCondition($field, $andCondition);

            if (count($orSegments) > 0)
            {
                foreach($orSegments as $orCondition)
                {
                    $this->orGroupStart();

                    $this->applyCondition($field, $andCondition);
                
                    $this->groupEnd();
                }
            }
        }

        $this->groupEnd();
    
        return $this;
    }

    public function applyCondition(string $field, string $condition)
    {
        helper('string');

        if ($condition == $this->emptyCondition)
        {
            $this->where($field . ' IS NULL');

            $this->or_where($field, '');
        
            return $this;
        }

        if ($condition == $this->notEmptyCondition)
        {
            $this->where($field . ' IS NOT NULL');

            $this->where($field . ' !=', '');
        
            return $this;
        }

        if (string_is('\".+\"', $condition))
        {
            $condition = string_replace_first('"', '', $condition);

            $condition = string_replace_last('"', '', $condition);
        
            $this->where($field, $condition);

            return $this;
        }

        if (string_starts_with($condition, $this->likeCondition))
        {
            $this->like($field, $condition, 'before');

            return $this;
        }

        if (string_starts_with($condition, $this->notCondition))
        {
            $condition = string_replace_first($this->notCondition, '', $condition);

            $this->where($field . ' !=', $condition);
            
            return $this;
        }

        if (string_ends_with($condition, $this->likeCondition))
        {
            $this->like($field, $condition, 'after');

            return $this;
        }

        if (mb_strpos($condition, '%') !== false)
        {
            $this->like($field, $this->db->escapeLikeString($condition), 'both', false);

            return $this;
        }

        $this->like($field, $condition, 'both');

        return $this;
    }

}