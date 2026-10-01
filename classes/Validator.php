<?php
/**
 * Server-Side Input Validator
 */

class Validator {
    private array $data = [];
    private array $errors = [];
    private Database $db;

    public function __construct(array $data) {
        $this->data = $data;
        $this->db = Database::getInstance();
    }

    public function validate(array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                } else {
                    $ruleName = $rule;
                }

                $this->applyRule($field, $value, $ruleName, $params);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
                    $this->addError($field, "$label is required.");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$label must be a valid email address.");
                }
                break;

            case 'min':
                $min = (int) $params[0];
                if (is_int($value) || is_float($value)) {
                    if ($value < $min) {
                        $this->addError($field, "$label must be at least $min.");
                    }
                } else {
                    $strVal = (string)($value ?? '');
                    if (mb_strlen(trim($strVal)) < $min) {
                        $this->addError($field, "$label must be at least $min characters.");
                    }
                }
                break;

            case 'max':
                $max = (int) $params[0];
                if (is_int($value) || is_float($value)) {
                    if ($value > $max) {
                        $this->addError($field, "$label may not be greater than $max.");
                    }
                } else {
                    $strVal = (string)($value ?? '');
                    if (mb_strlen(trim($strVal)) > $max) {
                        $this->addError($field, "$label may not exceed $max characters.");
                    }
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "$label must be a number.");
                }
                break;

            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "$label must be a valid integer.");
                }
                break;

            case 'matches':
                $matchField = $params[0];
                $matchValue = $this->data[$matchField] ?? null;
                $matchLabel = ucfirst(str_replace('_', ' ', $matchField));
                if ($value !== $matchValue) {
                    $this->addError($field, "$label does not match $matchLabel.");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params, true)) {
                    $this->addError($field, "$label contains an invalid selection.");
                }
                break;

            case 'unique':
                // unique:table,column[,ignoreId,ignoreColumn]
                $table = $params[0];
                $column = $params[1] ?? $field;
                $ignoreId = $params[2] ?? null;
                $ignoreColumn = $params[3] ?? 'id';

                if (!empty($value)) {
                    $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = :val";
                    $queryParams = [':val' => $value];

                    if ($ignoreId !== null) {
                        $sql .= " AND `$ignoreColumn` != :ignoreId";
                        $queryParams[':ignoreId'] = $ignoreId;
                    }

                    $count = (int) $this->db->fetchColumn($sql, $queryParams);
                    if ($count > 0) {
                        $this->addError($field, "$label is already in use.");
                    }
                }
                break;

            case 'phone':
                if (!empty($value)) {
                    // Match BD & International phone numbers
                    if (!preg_match('/^(\+?[0-9]{1,4})?[\s.-]?[0-9]{6,14}$/', trim($value))) {
                        $this->addError($field, "$label must be a valid phone number.");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): ?string {
        return reset($this->errors) ?: null;
    }
}
