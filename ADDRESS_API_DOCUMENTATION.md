# Address Management API Documentation

## Overview
Address management system with polymorphic relationships supporting User and PSW models. Includes automatic default address management and geolocation support.

## Base URLs
- **User Addresses**: `/api/v1/addresses`
- **PSW Addresses**: `/api/v1/psw/addresses`

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Get All Addresses
**GET** `/api/v1/addresses` or `/api/v1/psw/addresses`

Get all addresses for the authenticated user.

**Response 200:**
```json
{
  "status": "success",
  "message": "Addresses retrieved successfully",
  "data": {
    "addresses": [
      {
        "id": 1,
        "addressable_type": "Modules\\Core\\Models\\User",
        "addressable_id": 1,
        "label": "HOME",
        "address_line": "123 Main Street, Apt 4B",
        "city": "New York",
        "province": "NY",
        "postal_code": "10001",
        "country_id": 1,
        "latitude": 40.7128,
        "longitude": -74.0060,
        "is_default": true,
        "created_at": "2025-11-27T05:00:00.000000Z",
        "updated_at": "2025-11-27T05:00:00.000000Z"
      }
    ]
  }
}
```

---

### 2. Create Address
**POST** `/api/v1/addresses` or `/api/v1/psw/addresses`

Create a new address for the authenticated user.

**Request Body:**
```json
{
  "label": "HOME",
  "address_line": "123 Main Street, Apt 4B",
  "city": "New York",
  "province": "NY",
  "postal_code": "10001",
  "country_id": 1,
  "latitude": 40.7128,
  "longitude": -74.0060,
  "is_default": true
}
```

**Validation Rules:**
- `label`: required, string, enum (HOME, OFFICE, COTTAGE, FAMILY, OTHER)
- `address_line`: required, string, max 500 characters
- `city`: required, string, max 100 characters
- `province`: required, string, max 100 characters
- `postal_code`: required, string, max 20 characters
- `country_id`: required, integer, must exist in countries table
- `latitude`: optional, numeric, between -90 and 90
- `longitude`: optional, numeric, between -180 and 180
- `is_default`: optional, boolean

**Response 201:**
```json
{
  "status": "success",
  "message": "Address created successfully",
  "data": {
    "address": {
      "id": 1,
      "addressable_type": "Modules\\Core\\Models\\User",
      "addressable_id": 1,
      "label": "HOME",
      "address_line": "123 Main Street, Apt 4B",
      "city": "New York",
      "province": "NY",
      "postal_code": "10001",
      "country_id": 1,
      "latitude": 40.7128,
      "longitude": -74.0060,
      "is_default": true,
      "created_at": "2025-11-27T05:00:00.000000Z",
      "updated_at": "2025-11-27T05:00:00.000000Z"
    }
  }
}
```

**Notes:**
- If `is_default` is true, all other addresses will be unmarked as default
- If this is the first address, it will automatically be set as default

---

### 3. Get Single Address
**GET** `/api/v1/addresses/{id}` or `/api/v1/psw/addresses/{id}`

Get a specific address by ID.

**Response 200:**
```json
{
  "status": "success",
  "message": "Address retrieved successfully",
  "data": {
    "address": {
      "id": 1,
      "addressable_type": "Modules\\Core\\Models\\User",
      "addressable_id": 1,
      "label": "HOME",
      "address_line": "123 Main Street, Apt 4B",
      "city": "New York",
      "province": "NY",
      "postal_code": "10001",
      "country_id": 1,
      "latitude": 40.7128,
      "longitude": -74.0060,
      "is_default": true,
      "created_at": "2025-11-27T05:00:00.000000Z",
      "updated_at": "2025-11-27T05:00:00.000000Z"
    }
  }
}
```

**Response 404:**
```json
{
  "status": "error",
  "message": "Address not found"
}
```

---

### 4. Update Address
**PUT** `/api/v1/addresses/{id}` or `/api/v1/psw/addresses/{id}`

Update an existing address.

**Request Body:**
```json
{
  "label": "OFFICE",
  "address_line": "456 Business Ave, Suite 100",
  "city": "New York",
  "province": "NY",
  "postal_code": "10002",
  "country_id": 1,
  "latitude": 40.7580,
  "longitude": -73.9855,
  "is_default": false
}
```

**Validation Rules:**
- All fields are optional (use `sometimes` validation)
- Same validation rules as create endpoint

**Response 200:**
```json
{
  "status": "success",
  "message": "Address updated successfully",
  "data": {
    "address": {
      "id": 1,
      "addressable_type": "Modules\\Core\\Models\\User",
      "addressable_id": 1,
      "label": "OFFICE",
      "address_line": "456 Business Ave, Suite 100",
      "city": "New York",
      "province": "NY",
      "postal_code": "10002",
      "country_id": 1,
      "latitude": 40.7580,
      "longitude": -73.9855,
      "is_default": false,
      "created_at": "2025-11-27T05:00:00.000000Z",
      "updated_at": "2025-11-27T05:30:00.000000Z"
    }
  }
}
```

**Notes:**
- Cannot unset default if it's the only address (will remain default)
- Setting as default will automatically unset other defaults

---

### 5. Delete Address
**DELETE** `/api/v1/addresses/{id}` or `/api/v1/psw/addresses/{id}`

Delete an address.

**Response 200:**
```json
{
  "status": "success",
  "message": "Address deleted successfully"
}
```

**Response 404:**
```json
{
  "status": "error",
  "message": "Address not found"
}
```

**Notes:**
- If deleted address was default, the next address will automatically become default

---

### 6. Set Address as Default
**POST** `/api/v1/addresses/{id}/set-default` or `/api/v1/psw/addresses/{id}/set-default`

Set a specific address as the default address.

**Response 200:**
```json
{
  "status": "success",
  "message": "Default address updated successfully"
}
```

**Response 404:**
```json
{
  "status": "error",
  "message": "Address not found"
}
```

**Notes:**
- Automatically unsets other addresses as default

---

### 7. Get Default Address
**GET** `/api/v1/addresses/default` or `/api/v1/psw/addresses/default`

Get the default address for the authenticated user.

**Response 200:**
```json
{
  "status": "success",
  "message": "Default address retrieved successfully",
  "data": {
    "address": {
      "id": 1,
      "addressable_type": "Modules\\Core\\Models\\User",
      "addressable_id": 1,
      "label": "HOME",
      "address_line": "123 Main Street, Apt 4B",
      "city": "New York",
      "province": "NY",
      "postal_code": "10001",
      "country_id": 1,
      "latitude": 40.7128,
      "longitude": -74.0060,
      "is_default": true,
      "created_at": "2025-11-27T05:00:00.000000Z",
      "updated_at": "2025-11-27T05:00:00.000000Z"
    }
  }
}
```

**Response 404:**
```json
{
  "status": "error",
  "message": "No default address found"
}
```

---

## Address Labels
Valid address labels (from `AddressLabel` enum):
- `HOME`
- `OFFICE`
- `COTTAGE`
- `FAMILY`
- `OTHER`

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "label": ["Invalid address label"],
    "address_line": ["Address line is required"],
    "country_id": ["Invalid country selected"]
  }
}
```

### Server Error (500)
```json
{
  "status": "error",
  "message": "Failed to create address",
  "error": "Database connection failed"
}
```

---

## Database Schema

### Addresses Table
```sql
CREATE TABLE addresses (
    id BIGSERIAL PRIMARY KEY,
    addressable_type VARCHAR(255) NOT NULL,
    addressable_id BIGINT NOT NULL,
    label VARCHAR(255) NOT NULL,
    address_line TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country_id BIGINT NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX addresses_addressable_index (addressable_type, addressable_id),
    INDEX addresses_default_index (is_default),
    INDEX addresses_postal_code_index (postal_code),
    INDEX addresses_country_id_index (country_id),
    FOREIGN KEY (country_id) REFERENCES countries(id)
);
```

### Countries Table
```sql
CREATE TABLE countries (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(3) UNIQUE NOT NULL,
    phone_code VARCHAR(10) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX countries_is_active_index (is_active)
);
```

---

## Model Relationships

### User/PSW Models
```php
use App\Shared\Traits\HasAddresses;

class User extends Authenticatable
{
    use HasAddresses;
    
    // Access all addresses
    $user->addresses;
    
    // Get default address
    $user->defaultAddress();
    
    // Get addresses by label
    $user->addressesByLabel('HOME');
    
    // Add new address
    $user->addAddress($data);
    
    // Set address as default
    $user->setDefaultAddress($addressId);
}
```

### Address Model
```php
// Get the owner (User or PSW)
$address->addressable;

// Get country
$address->country;

// Get full formatted address
$address->full_address;

// Check if has coordinates
$address->hasCoordinates();

// Get coordinates array
$address->coordinates; // ['latitude' => 40.7128, 'longitude' => -74.0060]
```

---

## Implementation Files

### Core Module
- **Model**: `app/Shared/Models/Address.php`
- **Trait**: `app/Shared/Traits/HasAddresses.php`
- **Enum**: `app/Shared/Enums/AddressLabel.php`
- **Repository Interface**: `Modules/Core/app/Contracts/Repositories/AddressRepositoryInterface.php`
- **Repository**: `Modules/Core/app/Repositories/AddressRepository.php`
- **Service**: `Modules/Core/app/Services/AddressService.php`
- **Controller**: `Modules/Core/app/Http/Controllers/AddressController.php`
- **Form Requests**: 
  - `Modules/Core/app/Http/Requests/StoreAddressRequest.php`
  - `Modules/Core/app/Http/Requests/UpdateAddressRequest.php`
- **Routes**: `Modules/Core/routes/api.php`
- **Migration**: `database/migrations/2025_11_27_045824_create_addresses_table.php`

### Country Support
- **Model**: `Modules/Core/app/Models/Country.php`
- **Migration**: `database/migrations/2025_11_27_050437_create_countries_table.php`
- **Seeder**: `database/seeders/CountrySeeder.php`

---

## Testing Examples

### Create Address (cURL)
```bash
curl -X POST http://localhost:8000/api/v1/addresses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "label": "HOME",
    "address_line": "123 Main Street, Apt 4B",
    "city": "New York",
    "province": "NY",
    "postal_code": "10001",
    "country_id": 1,
    "latitude": 40.7128,
    "longitude": -74.0060,
    "is_default": true
  }'
```

### Get All Addresses (cURL)
```bash
curl -X GET http://localhost:8000/api/v1/addresses \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Set Default Address (cURL)
```bash
curl -X POST http://localhost:8000/api/v1/addresses/1/set-default \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Notes
- All endpoints support both User (`/api/v1/addresses`) and PSW (`/api/v1/psw/addresses`) authentication
- Addresses are automatically scoped to the authenticated user
- Default address logic is handled automatically
- Geolocation (latitude/longitude) is optional but useful for map integrations
- Country relationships allow for proper internationalization