<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueUsernameAcrossUsers implements ValidationRule
{
    /**
     * @var array<string>
     */
    protected array $tables;

    protected string $column;

    protected ?string $ignoreTable;

    protected ?int $ignoreId;

    /**
     * @param  array<string>  $tables
     */
    public function __construct(array $tables, string $column = 'username', ?string $ignoreTable = null, ?int $ignoreId = null)
    {
        $this->tables = $tables;
        $this->column = $column;
        $this->ignoreTable = $ignoreTable;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
          return;
        }

        foreach ($this->tables as $table) {
            $query = DB::table($table)->where($this->column, $value);

            if ($this->ignoreTable === $table && $this->ignoreId !== null) {
                $query->where('id', '!=', $this->ignoreId);
            }

            if ($query->exists()) {
                $fail('The :attribute has already been taken across user accounts.');
                return;
            }
        }
    }
}


