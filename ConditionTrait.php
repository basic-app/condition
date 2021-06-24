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

    protected $notLikeCondition = '-%';

    protected $notCondition = '-';

    public function filterWhereCondition($field, ?string $condition = null)
    {
        if (is_array($field))
        {
            foreach($field as $key => $value)
            {
                $this->whereCondition($key, $value);
            }

            return $this;
        }
        
        return $this->whereCondition($field, $condition);
    }

    public function whereCondition(string $field, ?string $condition)
    {
        if (!$condition)
        {
            return $this;
        }

        $andSegments = explode($this->andConditionDevider, $condition);

        $this->groupStart();

        foreach($andSegments as $andSegment)
        {
            if (!$andSegment)
            {
                continue;
            }

            $orSegments = explode($this->orConditionDevider, $andSegment);

            $andCondition = array_shift($orSegments);

            $this->applyCondition($field, $andCondition);

            if (count($orSegments) > 0)
            {
                foreach($orSegments as $orCondition)
                {
                    $this->orGroupStart();

                    $this->applyCondition($field, $orCondition);
                
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

        if (string_starts_with($condition, $this->notLikeCondition))
        {
            $condition = string_replace_first($this->notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'before');

            return $this;
        }

        if (string_starts_with($condition, $this->likeCondition))
        {
            $condition = string_replace_first($this->likeCondition, '', $condition);

            $this->like($field, $condition, 'before');

            return $this;
        }

        if (string_ends_with($condition, $this->likeCondition))
        {
            $condition = string_replace_last($this->likeCondition, '', $condition);

            $this->like($field, $condition, 'after');

            return $this;
        }

        if (string_ends_with($condition, $this->notLikeCondition))
        {
            $condition = string_replace_last($this->notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'after');

            return $this;
        }

        if (string_starts_with($condition, $this->notCondition))
        {
            $condition = string_replace_first($this->notCondition, '', $condition);

            $this->where($field . ' !=', $condition);
            
            return $this;
        }

        $this->like($field, $condition, 'both');

        return $this;
    }

}