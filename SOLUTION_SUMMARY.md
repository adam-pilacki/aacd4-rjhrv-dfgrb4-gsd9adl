# Candy Machine - Solution Summary

## **How to Run the Command**
```bash
php bin/console purchase-candy
```

### **Interactive Flow:**
1. Select candy type from the menu
2. Enter quantity (default: 1)
3. Enter payment amount
4. View purchase result and change calculation

## **BONUS: The Tests :) (yes, tests are AI generated - run them only if you have all needed extensions installed)**

### **Run All Tests:**
```bash
./vendor/bin/phpunit
```

## **Solution Overview**

### **System Architecture**
```
src/
├── Machine/           # Business logic
├── Command/           # Console command
└── Exception/         # Custom exceptions
```

### **Key Components**

#### **1. CandyCatalog** (`src/Machine/CandyCatalog.php`)
- **Purpose**: Central repository for available candies and prices
- **Implementation**: Final class with const array for simplicity and performance
- **Why**: `final` prevents inheritance, `const` ensures immutability, validation methods for data safety

#### **2. PurchaseTransaction** (`src/Machine/PurchaseTransaction.php`)
- **Purpose**: Value Object representing purchase transaction
- **Implementation**: Immutable object with constructor validation
- **Why**: Fail-fast principle, thread-safety, single responsibility

#### **3. PurchasedItem** (`src/Machine/PurchasedItem.php`)
- **Purpose**: Transaction result with calculated change
- **Implementation**: Value Object with `getUnitPrice()` method
- **Why**: Proper encapsulation, business logic in right place

#### **4. CandyMachine** (`src/Machine/CandyMachine.php`)
- **Purpose**: Core business logic of the machine
- **Implementation**: Service class with Dependency Injection
- **Why**: Testability, loose coupling, single responsibility

#### **5. PurchaseCandyCommand** (`src/Command/PurchaseCandyCommand.php`)
- **Purpose**: User interface as console command
- **Implementation**: Symfony Console Command with DI
- **Why**: Industry standard, testability, better UX

### **Key Technical Decisions**

#### **1. Dependency Injection Pattern**
```php
public function __construct(MachineInterface $machine, CandyCatalogInterface $catalog)
```
**Why**: Testability, loose coupling, single responsibility

#### **2. Immutable Value Objects**
```php
final class PurchaseTransaction implements PurchaseTransactionInterface
{
    // No setters - immutability
}
```
**Why**: Predictability, proper domain modeling

#### **3. Greedy Algorithm for Change**
```php
private function calculateChange(float $amount): array
{
    $availableCoins = [2.00, 1.00, 0.50, 0.20, 0.10, 0.05, 0.02, 0.01];
    // Greedy algorithm - always picks largest possible coin
}
```
**Why**: Optimality, simplicity, industry standard approach

### **Applied Design Patterns**

- **Value Object Pattern** - Immutable transaction objects (`PurchaseTransaction`, `PurchasedItem`)
- **Dependency Injection Pattern** - Constructor-based dependency injection
- **Command Pattern** - Symfony Console Command implementation
- **Interface Segregation** - Small, focused interfaces (`MachineInterface`, `PurchaseTransactionInterface`)

### **Architectural Approaches**

- **Service Layer** - Business logic in `CandyMachine` service
- **Data Access Layer** - `CandyCatalog` as data provider (not a true Repository pattern)
- **Layered Architecture** - Clear separation between Command, Service, and Data layers

### **SOLID Principles Implementation**

- **SRP**: Each class has single responsibility
- **OCP**: Open for extension, closed for modification  
- **LSP**: All interface implementations are fully compatible
- **ISP**: Small, specialized interfaces
- **DIP**: Fully implemented - Command depends on interfaces, not concrete classes

### **Validation**
```php
private function validateType(string $type): void
{
    if (empty(trim($type))) {
        throw new \InvalidArgumentException('Candy type cannot be empty');
    }
}
```

#### **Custom Exceptions:**
- `InsufficientPaymentException`
- `InvalidCandyTypeException`

#### **Fail-Fast Principle:**
- Validation at earliest possible stage
- Clear error messages for users

### **Performance & Optimization**

#### **Change Calculation Algorithm:**
- O(n) complexity
- Predefined coins in descending order
- Optimal use of largest available coins

#### **Memory Management:**
- Immutable objects
- Final classes
- Minimal memory allocations

### **System Extensibility**

#### **Adding New Candies:**
```php
private const CANDIES = [
    'new_candy' => 5.99,
    // Just add new line
];
```

#### **Adding New Machine Types:**
```php
class PremiumCandyMachine implements MachineInterface
{
    // Could implement premium features like loyalty points
    // Currently not implemented - would require additional interfaces
}
```
