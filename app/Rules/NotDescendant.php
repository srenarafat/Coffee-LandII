<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDescendant implements ValidationRule
{
    /**
     * The category being validated against.
     */
    protected ?Category $category;

    public function __construct(?Category $category)
    {
        $this->category = $category;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value || !$this->category || !$this->category->id) {
            return;
        }

        if ((int) $value === (int) $this->category->id) {
            $fail('The parent category cannot be the category itself or one of its descendants.');
            return;
        }

        if ($this->isDescendant($this->category, (int) $value)) {
            $fail('The parent category cannot be the category itself or one of its descendants.');
        }
    }

    /**
     * Determine if the given id is a descendant of the category.
     */
    protected function isDescendant(Category $category, int $id): bool
    {
        foreach ($category->children as $child) {
            if ($child->id == $id || $this->isDescendant($child, $id)) {
                return true;
            }
        }

        return false;
    }
}