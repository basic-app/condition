<?php
/**
 * @author Basic App Dev Team <dev@basic-app.com>
 * @license MIT
 * @link https://basic-app.com
 */
namespace BasicApp\Condition;

trait ConditionTrait
{

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
        $andConditionDevider = property_exists($this, 'andConditionDevider') ? $this->andConditionDevider : '&';

        $orConditionDevider = property_exists($this, 'orConditionDevider') ? $this->orConditionDevider : '|';

        if (!$condition)
        {
            return $this;
        }

        $andSegments = explode($andConditionDevider, $condition);

        $this->groupStart();

        foreach($andSegments as $andSegment)
        {
            if (!$andSegment)
            {
                continue;
            }

            $orSegments = explode($orConditionDevider, $andSegment);

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

        $emptyCondition = property_exists($this, 'emptyCondition') ? $this->emptyCondition : '/';

        if ($condition == $emptyCondition)
        {
            $this->where($field . ' IS NULL');

            $this->or_where($field, '');
        
            return $this;
        }

        $notEmptyCondition = property_exists($this, 'notEmptyCondition') ? $this->notEmptyCondition : '-/';

        if ($condition == $notEmptyCondition)
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

        $notLikeCondition = property_exists($this, 'notLikeCondition') ? $this->notLikeCondition : '-%';

        if (string_starts_with($condition, $notLikeCondition))
        {
            $condition = string_replace_first($notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'before');

            return $this;
        }

        if (string_ends_with($condition, $notLikeCondition))
        {
            $condition = string_replace_last($notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'after');

            return $this;
        }

        $likeCondition = property_exists($this, 'likeCondition') ? $this->likeCondition : '%';

        if (string_starts_with($condition, $likeCondition))
        {
            $condition = string_replace_first($likeCondition, '', $condition);

            $this->like($field, $condition, 'before');

            return $this;
        }

        if (string_ends_with($condition, $likeCondition))
        {
            $condition = string_replace_last($likeCondition, '', $condition);

            $this->like($field, $condition, 'after');

            return $this;
        }

        $notCondition = property_exists($this, 'notCondition') ? $this->notCondition : '-';

        if (string_starts_with($condition, $notCondition))
        {
            $condition = string_replace_first($notCondition, '', $condition);

            $this->where($field . ' !=', $condition);
            
            return $this;
        }

        $this->like($field, $condition, 'both');

        return $this;
    }

}