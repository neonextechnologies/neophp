# Validation Classes

Complete reference for validation-related classes.

## Validator

Input validation interface.

### Factory Methods

#### `make($data, $rules, $messages = [], $attributes = [])`

```php
$validator = Validator::make($request->all(), [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users'
]);
```

#### `validate($data, $rules, $messages = [], $attributes = [])`

```php
$validated = Validator::validate($request->all(), [
    'title' => 'required|max:255',
    'content' => 'required'
]);
```

### Validation Methods

#### `fails()`

```php
if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}
```

#### `passes()`

```php
if ($validator->passes()) {
    // Validation passed
}
```

#### `errors()`

```php
$errors = $validator->errors();
```

#### `validated()`

```php
$validated = $validator->validated();
```

#### `sometimes($attribute, $rules, $callback)`

```php
$validator->sometimes('reason', 'required|max:500', function($input) {
    return $input->status === 'rejected';
});
```

#### `after($callback)`

```php
$validator->after(function($validator) {
    if ($this->somethingElseIsInvalid()) {
        $validator->errors()->add('field', 'Something is wrong with this field!');
    }
});
```

---

## Validation Rules

All available validation rules.

### Required Rules

#### `required`

Field must be present and not empty.

```php
'name' => 'required'
```

#### `required_if:anotherfield,value`

Required if another field equals a value.

```php
'reason' => 'required_if:status,rejected'
```

#### `required_unless:anotherfield,value`

Required unless another field equals a value.

```php
'tax_id' => 'required_unless:country,US'
```

#### `required_with:foo,bar,...`

Required if any other specified fields are present.

```php
'postal_code' => 'required_with:address,city'
```

#### `required_without:foo,bar,...`

Required if any other specified fields are not present.

```php
'phone' => 'required_without:email'
```

#### `required_with_all:foo,bar,...`

Required if all other specified fields are present.

```php
'apartment' => 'required_with_all:address,city,postal_code'
```

#### `required_without_all:foo,bar,...`

Required if all other specified fields are not present.

```php
'email' => 'required_without_all:phone,mobile'
```

#### `present`

Field must be present but can be empty.

```php
'terms' => 'present'
```

#### `filled`

Field must not be empty when present.

```php
'username' => 'filled'
```

### Type Rules

#### `string`

Must be a string.

```php
'name' => 'string'
```

#### `integer`

Must be an integer.

```php
'age' => 'integer'
```

#### `numeric`

Must be numeric.

```php
'amount' => 'numeric'
```

#### `boolean`

Must be boolean (true, false, 1, 0, "1", "0").

```php
'active' => 'boolean'
```

#### `array`

Must be an array.

```php
'items' => 'array'
```

#### `email`

Must be a valid email address.

```php
'email' => 'email'
```

#### `url`

Must be a valid URL.

```php
'website' => 'url'
```

#### `ip`

Must be an IP address.

```php
'server_ip' => 'ip'
```

#### `ipv4`

Must be an IPv4 address.

```php
'ipv4_address' => 'ipv4'
```

#### `ipv6`

Must be an IPv6 address.

```php
'ipv6_address' => 'ipv6'
```

#### `json`

Must be valid JSON.

```php
'metadata' => 'json'
```

#### `uuid`

Must be a valid UUID.

```php
'id' => 'uuid'
```

### Size Rules

#### `min:value`

Minimum value (string length, numeric value, array count, file size KB).

```php
'name' => 'min:3',
'age' => 'min:18',
'items' => 'min:2',
'avatar' => 'min:100'  // KB
```

#### `max:value`

Maximum value.

```php
'username' => 'max:20',
'price' => 'max:1000',
'tags' => 'max:5',
'upload' => 'max:2048'  // 2MB
```

#### `size:value`

Exact size.

```php
'code' => 'size:6',
'amount' => 'size:100',
'colors' => 'size:3'
```

#### `between:min,max`

Between min and max.

```php
'age' => 'between:18,65',
'score' => 'between:0,100'
```

### String Rules

#### `alpha`

Only alphabetic characters.

```php
'name' => 'alpha'
```

#### `alpha_dash`

Alpha-numeric, dashes, and underscores.

```php
'username' => 'alpha_dash'
```

#### `alpha_num`

Alpha-numeric characters.

```php
'code' => 'alpha_num'
```

#### `starts_with:foo,bar,...`

Must start with one of the given values.

```php
'phone' => 'starts_with:+1,+44'
```

#### `ends_with:foo,bar,...`

Must end with one of the given values.

```php
'domain' => 'ends_with:.com,.net,.org'
```

#### `regex:pattern`

Must match regex pattern.

```php
'username' => 'regex:/^[a-z0-9_]+$/'
```

### Comparison Rules

#### `same:field`

Must match another field.

```php
'password_confirmation' => 'same:password'
```

#### `different:field`

Must be different from another field.

```php
'new_password' => 'different:old_password'
```

#### `confirmed`

Must have matching confirmation field (field_confirmation).

```php
'password' => 'confirmed'  // Requires password_confirmation
```

#### `gt:field`

Greater than another field.

```php
'end_date' => 'gt:start_date'
```

#### `gte:field`

Greater than or equal to another field.

```php
'max_price' => 'gte:min_price'
```

#### `lt:field`

Less than another field.

```php
'discount' => 'lt:price'
```

#### `lte:field`

Less than or equal to another field.

```php
'min_price' => 'lte:max_price'
```

### Date Rules

#### `date`

Must be a valid date.

```php
'birth_date' => 'date'
```

#### `date_format:format`

Must match date format.

```php
'appointment' => 'date_format:Y-m-d H:i'
```

#### `before:date`

Must be before a date.

```php
'start_date' => 'before:2024-12-31'
```

#### `before_or_equal:date`

Must be before or equal to a date.

```php
'deadline' => 'before_or_equal:today'
```

#### `after:date`

Must be after a date.

```php
'expiry_date' => 'after:today'
```

#### `after_or_equal:date`

Must be after or equal to a date.

```php
'start_date' => 'after_or_equal:today'
```

### Database Rules

#### `unique:table,column,except,idColumn`

Must be unique in database table.

```php
'email' => 'unique:users',
'email' => 'unique:users,email_address',
'email' => 'unique:users,email,' . $user->id,
'email' => 'unique:users,email,' . $user->id . ',id'
```

#### `exists:table,column`

Must exist in database table.

```php
'user_id' => 'exists:users,id',
'category_id' => 'exists:categories'
```

### File Rules

#### `file`

Must be a successfully uploaded file.

```php
'document' => 'file'
```

#### `image`

Must be an image (jpeg, png, bmp, gif, svg, webp).

```php
'avatar' => 'image'
```

#### `mimes:foo,bar,...`

Must have one of the MIME types.

```php
'document' => 'mimes:pdf,doc,docx'
```

#### `mimetypes:text/plain,...`

Must match MIME type.

```php
'video' => 'mimetypes:video/avi,video/mpeg'
```

#### `dimensions:min_width=100,max_height=500`

Image dimensions constraints.

```php
'avatar' => 'dimensions:min_width=100,min_height=100,max_width=500,max_height=500'
```

### Inclusion Rules

#### `in:foo,bar,...`

Must be in list of values.

```php
'status' => 'in:active,pending,completed'
```

#### `not_in:foo,bar,...`

Must not be in list of values.

```php
'username' => 'not_in:admin,root,system'
```

#### `in_array:anotherfield.*`

Must exist in another array field.

```php
'selected_option' => 'in_array:available_options.*'
```

### Array Rules

#### `array`

Must be an array.

```php
'items' => 'array'
```

#### `array:foo,bar,...`

Array can only contain specific keys.

```php
'user' => 'array:name,email,age'
```

#### `distinct`

Array values must be unique.

```php
'tags.*' => 'distinct'
```

### Nullable & Optional

#### `nullable`

Field may be null.

```php
'middle_name' => 'nullable|string'
```

#### `sometimes`

Field is validated only when present.

```php
'optional_field' => 'sometimes|required|string'
```

---

## ValidationException

Exception thrown when validation fails.

### Methods

#### `errors()`

```php
try {
    Validator::validate($data, $rules);
} catch (ValidationException $e) {
    $errors = $e->errors();
}
```

#### `validator()`

```php
$validator = $e->validator();
```

---

## MessageBag

Validation error message container.

### Methods

#### `add($key, $message)`

```php
$errors->add('email', 'Email is already taken');
```

#### `has($key)`

```php
if ($errors->has('email')) {
    // Has email error
}
```

#### `first($key)`

```php
$message = $errors->first('email');
```

#### `get($key)`

```php
$messages = $errors->get('email');
```

#### `all()`

```php
$allErrors = $errors->all();
```

#### `any()`

```php
if ($errors->any()) {
    // Has any errors
}
```

#### `count()`

```php
$count = $errors->count();
```

#### `isEmpty()`

```php
if ($errors->isEmpty()) {
    // No errors
}
```

#### `isNotEmpty()`

```php
if ($errors->isNotEmpty()) {
    // Has errors
}
```

---

## Custom Validation Rules

### Creating Custom Rules

```php
<?php

namespace App\Rules;

use Neo\Validation\Rule;

class Uppercase implements Rule
{
    public function passes($attribute, $value): bool
    {
        return strtoupper($value) === $value;
    }
    
    public function message(): string
    {
        return 'The :attribute must be uppercase.';
    }
}
```

### Using Custom Rules

```php
use App\Rules\Uppercase;

$validator = Validator::make($request->all(), [
    'code' => ['required', new Uppercase()]
]);
```

### Closure Rules

```php
$validator = Validator::make($request->all(), [
    'title' => [
        'required',
        function($attribute, $value, $fail) {
            if ($value === 'foo') {
                $fail('The ' . $attribute . ' is invalid.');
            }
        }
    ]
]);
```

---

## Custom Error Messages

### Per-Attribute Messages

```php
$messages = [
    'email.required' => 'We need your email address!',
    'email.email' => 'Please provide a valid email address.',
    'password.min' => 'Your password must be at least :min characters.'
];

$validator = Validator::make($data, $rules, $messages);
```

### Custom Attribute Names

```php
$attributes = [
    'email' => 'email address',
    'password' => 'secret code'
];

$validator = Validator::make($data, $rules, $messages, $attributes);
```

---

## Advanced Validation

### Conditional Rules

```php
$validator = Validator::make($request->all(), [
    'status' => 'required|in:published,draft',
    'publish_date' => 'required_if:status,published|date'
]);
```

### Array Validation

```php
$validator = Validator::make($request->all(), [
    'users' => 'required|array|min:1',
    'users.*.name' => 'required|string|max:255',
    'users.*.email' => 'required|email|unique:users'
]);
```

### Nested Array Validation

```php
$validator = Validator::make($request->all(), [
    'orders' => 'required|array',
    'orders.*.items' => 'required|array',
    'orders.*.items.*.product_id' => 'required|exists:products,id',
    'orders.*.items.*.quantity' => 'required|integer|min:1'
]);
```

### Complex Validation Example

```php
$validator = Validator::make($request->all(), [
    // User info
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email,' . $userId,
    'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
    
    // Address
    'address.street' => 'required|string|max:255',
    'address.city' => 'required|string|max:100',
    'address.postal_code' => 'required|regex:/^[0-9]{5}$/',
    
    // Order items
    'items' => 'required|array|min:1',
    'items.*.product_id' => 'required|exists:products,id',
    'items.*.quantity' => 'required|integer|min:1|max:100',
    'items.*.options' => 'nullable|array',
    'items.*.options.*.name' => 'required|string',
    'items.*.options.*.value' => 'required|string',
    
    // Payment
    'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
    'card_number' => 'required_if:payment_method,credit_card|regex:/^[0-9]{16}$/',
    'expiry_date' => 'required_if:payment_method,credit_card|date_format:m/y|after:today',
    
    // Terms
    'terms_accepted' => 'required|accepted'
], [
    'email.unique' => 'This email is already registered.',
    'items.*.product_id.exists' => 'Invalid product selected.',
    'card_number.regex' => 'Please enter a valid 16-digit card number.'
]);

if ($validator->fails()) {
    return response()->json([
        'errors' => $validator->errors()
    ], 422);
}

$validated = $validator->validated();
```

---

## FormRequest

Form request validation class.

### Example Implementation

```php
<?php

namespace App\Requests;

use Neo\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }
    
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'published_at' => 'nullable|date|after:now'
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your post.',
            'category_id.exists' => 'Selected category does not exist.'
        ];
    }
    
    public function attributes(): array
    {
        return [
            'published_at' => 'publish date'
        ];
    }
}
```

### Using FormRequest

```php
public function store(StorePostRequest $request)
{
    $validated = $request->validated();
    
    $post = Post::create($validated);
    
    return redirect()->route('posts.show', $post);
}
```

---

## Next Steps

- [Cache Classes](cache.md)
- [Queue Classes](queue.md)
- [Mail Classes](mail.md)
