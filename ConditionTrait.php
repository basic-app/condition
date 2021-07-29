<?php
/**
 * @author Basic App Dev Team <dev@basic-app.com>
 * @license MIT
 * @link https://basic-app.com
 */
namespace BasicApp\Condition;

use BasicApp\String\StringHelper;

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

        if (StringHelper::is('\".+\"', $condition))
        {
            $condition = StringHelper::replaceFirst('"', '', $condition);

            $condition = StringHelper::replaceLast('"', '', $condition);
        
            $this->where($field, $condition);

            return $this;
        }

        $notLikeCondition = property_exists($this, 'notLikeCondition') ? $this->notLikeCondition : '-%';

        if (StringHelper::startsWith($condition, $notLikeCondition))
        {
            $condition = StringHelper::replaceFirst($notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'before');

            return $this;
        }

        if (StringHelper::endsWith($condition, $notLikeCondition))
        {
            $condition = StringHelper::replaceLast($notLikeCondition, '', $condition);

            $this->notLike($field, $condition, 'after');

            return $this;
        }

        $likeCondition = property_exists($this, 'likeCondition') ? $this->likeCondition : '%';

        if (StringHelper::startsWith($condition, $likeCondition))
        {
            $condition = StringHelper::replaceFirst($likeCondition, '', $condition);

            $this->like($field, $condition, 'before');

            return $this;
        }

        if (StringHelper::endsWith($condition, $likeCondition))
        {
            $condition = StringHelper::replaceLast($likeCondition, '', $condition);

            $this->like($field, $condition, 'after');

            return $this;
        }

        $notCondition = property_exists($this, 'notCondition') ? $this->notCondition : '-';

        if (StringHelper::startsWith($condition, $notCondition))
        {
            $condition = StringHelper::replaceFirst($notCondition, '', $condition);

            $this->where($field . ' !=', $condition);
            
            return $this;
        }

        $this->like($field, $condition, 'both');

        return $this;
    }

}