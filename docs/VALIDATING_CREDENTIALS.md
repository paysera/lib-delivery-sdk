# Validating Credentials

This guide describes how to validate Paysera Delivery API credentials (project ID and password) during initial setup or configuration.

## Overview

The credentials validation feature allows you to verify if the provided `project_id` and `password` are valid. This is useful for:

- Validating user input in configuration forms
- Troubleshooting authentication issues
- Implementing setup wizards with credential verification

## How it works

The validation uses a public (unauthenticated) API endpoint `/rest/v1/projects/validate-credentials` that:
- Returns **HTTP 204** (No Content) if credentials are valid
- Returns **HTTP 401** (Unauthorized) if credentials are invalid
- Returns **HTTP 429** (Too Many Requests) if rate limit is exceeded

## Usage

### Basic Example

```php
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;

// Create credentials object
$credentials = new ProjectCredentials([
    'project_id' => '123456',  // Note: must be a string
    'password' => 'your-api-password'
]);

// Validate credentials
try {
    $isValid = $deliveryFacade->validateCredentials($credentials);

    if ($isValid) {
        echo "Credentials are valid!";
    } else {
        echo "Credentials are invalid!";
    }
} catch (RateLimitExceededException $exception) {
    // Handle rate limit (HTTP 429)
    echo "Too many validation attempts. Please try again later.";
} catch (MerchantClientNotFoundException $exception) {
    // Handle merchant client configuration error
    echo "Service configuration error. Please contact support.";
} catch (CredentialsValidationException $exception) {
    // Handle other validation errors (HTTP 401, 403, etc.)
    echo "Validation failed: " . $exception->getMessage();
}
```

### Integration in Configuration Forms

Example of validating credentials in a settings form:

```php
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;

// In your settings controller
public function saveSettings(array $postData): void
{
    $projectId = $postData['project_id'];
    $password = $postData['project_password'];

    // Create credentials object
    $credentials = new ProjectCredentials([
        'project_id' => (string) $projectId,  // Must be string
        'password' => $password
    ]);

    // Validate credentials before saving
    try {
        $isValid = $this->deliveryFacade->validateCredentials($credentials);

        if (!$isValid) {
            throw new InvalidArgumentException('Invalid Paysera Delivery API credentials');
        }

        // Credentials are valid, save settings
        $this->settingsRepository->save([
            'project_id' => $projectId,
            'project_password' => $password,
        ]);

        $this->showSuccessMessage('Settings saved successfully!');

    } catch (RateLimitExceededException $exception) {
        // Handle rate limit (HTTP 429)
        $this->showErrorMessage('Too many validation attempts. Please wait 5 minutes and try again.');
        $this->logger->warning('Rate limit exceeded during credentials validation', [
            'project_id' => $projectId,
            'exception' => $exception
        ]);
    } catch (MerchantClientNotFoundException $exception) {
        // Handle configuration error
        $this->showErrorMessage('Service configuration error. Please contact support.');
        $this->logger->error('Merchant client not found', ['exception' => $exception]);
    } catch (CredentialsValidationException $exception) {
        // Handle validation errors (invalid credentials, network issues, etc.)
        $this->showErrorMessage('Could not validate credentials: ' . $exception->getMessage());
        $this->logger->error('Credentials validation failed', [
            'project_id' => $projectId,
            'exception' => $exception
        ]);
    }
}
```

### Setup Wizard Example

Using validation in a multi-step setup wizard:

```php
use Paysera\DeliveryApi\MerchantClient\Entity\ProjectCredentials;
use Paysera\DeliverySdk\Exception\CredentialsValidationException;
use Paysera\DeliverySdk\Exception\RateLimitExceededException;
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;

public function setupWizardStep1(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $projectId = $_POST['project_id'];
        $password = $_POST['password'];

        // Create credentials object
        $credentials = new ProjectCredentials([
            'project_id' => (string) $projectId,
            'password' => $password
        ]);

        try {
            $isValid = $this->deliveryFacade->validateCredentials($credentials);

            if ($isValid) {
                // Store validated credentials in session
                $_SESSION['delivery_credentials'] = [
                    'project_id' => $projectId,
                    'password' => $password,
                    'validated_at' => time(),
                ];

                // Redirect to next step
                header('Location: /setup/step2');
                exit;
            } else {
                $this->showError('Invalid credentials. Please check and try again.');
            }
        } catch (RateLimitExceededException $exception) {
            $this->showError('Too many attempts. Please wait a few minutes and try again.');
        } catch (MerchantClientNotFoundException $exception) {
            $this->showError('Service configuration error. Please contact support.');
        } catch (CredentialsValidationException $exception) {
            $this->showError('Validation error: ' . $exception->getMessage());
        }
    }

    $this->renderForm();
}
```

## Response Handling

### Success (Valid Credentials)
When credentials are valid, the method returns `true`:
```php
$credentials = new ProjectCredentials([
    'project_id' => '123456',
    'password' => 'your-password'
]);

$isValid = $deliveryFacade->validateCredentials($credentials);
// $isValid === true
```

### Invalid Credentials
When credentials are invalid, the method returns `false`:
```php
$isValid = $deliveryFacade->validateCredentials($credentials);
// $isValid === false
```

### Exception Handling

The method can throw three types of exceptions:

#### 1. RateLimitExceededException (HTTP 429)
Thrown when too many validation attempts are made in a short time:
```php
use Paysera\DeliverySdk\Exception\RateLimitExceededException;

try {
    $isValid = $deliveryFacade->validateCredentials($credentials);
} catch (RateLimitExceededException $exception) {
    // Rate limit exceeded - wait 5 minutes before retrying
    echo "Too many validation attempts. Please wait and try again.";

    // Get the original HTTP exception if needed
    $httpException = $exception->getPrevious();
    // $httpException->getResponse()->getStatusCode() === 429
}
```

#### 2. MerchantClientNotFoundException
Thrown when the merchant client cannot be instantiated (configuration error):
```php
use Paysera\DeliverySdk\Exception\MerchantClientNotFoundException;

try {
    $isValid = $deliveryFacade->validateCredentials($credentials);
} catch (MerchantClientNotFoundException $exception) {
    // Service configuration error - check your SDK setup
    echo "Service configuration error. Please contact support.";
    $this->logger->critical('Merchant client not found', ['exception' => $exception]);
}
```

#### 3. CredentialsValidationException (HTTP 401, 403, etc.)
Thrown for other validation errors (invalid credentials, network issues):
```php
use Paysera\DeliverySdk\Exception\CredentialsValidationException;

try {
    $isValid = $deliveryFacade->validateCredentials($credentials);
} catch (CredentialsValidationException $exception) {
    // Invalid credentials or other HTTP errors
    echo "Validation failed: " . $exception->getMessage();
    // Message format: "401 Unauthorized" or "403 Forbidden"

    // Get the original HTTP exception
    $httpException = $exception->getPrevious();
}
```

### Comprehensive Error Handling
Best practice: catch exceptions in order from most specific to most general:
```php
try {
    $isValid = $deliveryFacade->validateCredentials($credentials);

    if ($isValid) {
        // Save credentials and proceed
    } else {
        // Should not happen - API returns HTTP 401 for invalid credentials
        // which throws CredentialsValidationException
    }
} catch (RateLimitExceededException $e) {
    // User action: wait 5 minutes
    $this->showUserError('Too many attempts. Please try again in 5 minutes.');
} catch (MerchantClientNotFoundException $e) {
    // Developer action: check SDK configuration
    $this->logger->critical('SDK configuration error', ['exception' => $e]);
    $this->showUserError('Service unavailable. Please contact support.');
} catch (CredentialsValidationException $e) {
    // User action: check credentials
    $this->showUserError('Invalid credentials: ' . $e->getMessage());
}
```

## Important Notes

1. **ProjectCredentials Type**: The method accepts `ProjectCredentials` object (not primitive types). The `project_id` field **must be a string**, not an integer:
   ```php
   // ✅ Correct
   new ProjectCredentials(['project_id' => '123456', 'password' => 'pass']);

   // ❌ Wrong - will cause type error
   $deliveryFacade->validateCredentials(123456, 'pass');
   ```

2. **Usage Restrictions**: This method is intended for one-time validation during setup or configuration changes only. **Do not use it before regular operations** like creating delivery orders or fetching delivery gateways, as it will result in unnecessary API calls and may trigger rate limits.

3. **Rate Limiting**: The API has rate limits. Catching `RateLimitExceededException` is important for production use. Implement exponential backoff or wait 5 minutes before retrying.

4. **No Authentication Required**: This endpoint is public and doesn't require MAC authentication. The SDK handles this automatically by using a public client.

5. **Logging**: All validation attempts (successful and failed) are logged at two levels:
   - `DeliveryApiClient` - logs HTTP-level details
   - `DeliveryOrderService` - logs operation-level details with specific messages for each exception type

   Check your logs for troubleshooting. Example log messages:
   - `"Credentials validation failed for project 123456: HTTP 429"` (rate limit)
   - `"Operation 'validate_credentials' for project id 123456 failed due to rate limit."` (service layer)

6. **Security**: Never expose credentials in client-side code or logs. Always validate credentials on the server side.

## Architecture

The validation flows through the following layers:

1. **DeliveryFacade** - Entry point, accepts `ProjectCredentials`
2. **DeliveryOrderService** - Business logic and comprehensive logging (logs all exception types)
3. **DeliveryApiClient** - Exception transformation and HTTP-level logging
4. **DeliveryOrderApiClient** - Direct API communication (passes `ProjectCredentials` to API)
5. **MerchantClientProvider** - HTTP client creation (without authentication)

Each layer adds appropriate logging and error handling. The `ProjectCredentials` object is passed through all layers without transformation until it reaches the API client.

## Testing

Unit tests for credential validation are located in:
- `tests/DeliveryFacadeTest.php`
- `tests/Service/DeliveryOrderServiceTest.php`
- `tests/Client/DeliveryApiClientTest.php`
- `tests/Client/DeliveryOrderApiClientTest.php`
- `tests/Client/Provider/MerchantClientProviderTest.php`

Run tests with:
```bash
vendor/bin/phpunit --filter validateCredentials
```

## See Also

- [Integration with SDK](BEFORE_USING.md) - Setting up the SDK
- [Delivery orders creating](DELIVERY_ORDER_CREATING.md) - Creating delivery orders
- [Delivery orders updating](DELIVERY_ORDER_UPDATING.md) - Updating existing orders
