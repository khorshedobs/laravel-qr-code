# Laravel QR Code Generator

A simple and powerful QR Code generator for Laravel 11 & 12, developed by **The RS Software, BD**.

## Requirements

- PHP >= 8.2
- Laravel >= 10.0

## Installation

You can install the package via composer:

```bash
composer require rssoftbd/laravel-qr-code
```

## Usage

The package provides a fluent interface for generating QR codes. You can use the `QrCode` facade.

### Basic Usage

```php
use RSSoftBD\QrCode\Facades\QrCode;

return QrCode::render('Hello World');
```

This will return the SVG string of the QR code.

### Customizing Size and Color

```php
use RSSoftBD\QrCode\Facades\QrCode;

return QrCode::setDimensions(300)
    ->setForegroundColor(255, 0, 0) // RGB Red
    ->setBackgroundColor(255, 255, 255) // RGB White
    ->render('Hello World');
```

### Changing Format

Supported formats: `svg`, `png`, `eps`.

```php
return QrCode::setOutputFormat('png')
    ->render('Hello World');
```

> **Note:** The `png` format requires the `imagick` extension.

### Styling (Shapes and Eyes)

You can customize the appearance of the QR code modules (shapes) and eyes.

**Available Shapes:**
`square`, `dot`, `round`, `PlusBold`, `CutCorner`, `XBold`, `Circle`, `XCross`, `XCurvy`, `Rhombus`, `SquareElastic`, `Blossom`, `Amour`, `Hex`, `SquircleInverted`, `Foliage`, `Shuriken`, `Octo`, `SquareRandom`, `CrossRounded`, `Soft`, `Defender`, `Sparkle`, `SquareSpaced`, `Stellar`, `Solar`, `Drop`, `Pyramid`, `Cloud`, `Pill`, `Burst`.

**Available Eyes:**
`square`, `circle`, `PlusBold`, `XCross`, `XCurvy`, `Rhombus`, `SquareElastic`, `Blossom`, `Amour`, `Hex`, `SquircleInverted`, `Foliage`, `Shuriken`, `Octo`, `CrossRounded`, `Soft`, `Defender`, `Sparkle`, `Stellar`, `Solar`, `StellarFat`, `Cloud`, `Pill`, `Burst`.

```php
return QrCode::setModuleStyle('round', 0.5) // Shape and size (0-1)
    ->setEyeStyle('circle')
    ->render('Hello World');
```

### Advanced Coloring

You can set the foreground, background, and individual eye colors. You can also use gradients.

**Basic Colors:**

```php
return QrCode::setForegroundColor(0, 0, 255) // Blue Foreground
    ->setBackgroundColor(255, 255, 200) // Light Yellow Background
    ->render('Hello World');
```

**Eye Colors:**
You can color each eye (0, 1, 2) individually.

```php
// setEyeColor(eyeIndex, innerR, innerG, innerB, innerA, outerR, outerG, outerB, outerA)
return QrCode::setEyeColor(0, 255, 0, 0, 0, 0, 0, 0) // Eye 0: Red Inner, Black Outer
    ->setEyeColor(1, 0, 255, 0, 0, 0, 0, 0) // Eye 1: Green Inner, Black Outer
    ->setEyeColor(2, 0, 0, 255, 0, 0, 0, 0) // Eye 2: Blue Inner, Black Outer
    ->render('Hello World');
```

**Gradients:**

```php
// setGradient(startR, startG, startB, endR, endG, endB, type)
// Types: 'vertical', 'horizontal', 'diagonal', 'inverse_diagonal', 'radial'
return QrCode::setGradient(255, 0, 0, 0, 0, 255, 'diagonal')
    ->render('Hello World');
```

### Margins (Frames)

You can add a margin around the QR code, which acts as a simple frame.

```php
return QrCode::setMargin(2) // 2 block margin
    ->render('Hello World');
```

### Adding a Logo (Merge)

You can merge an image (logo) into the center of the QR code.

```php
return QrCode::setOutputFormat('png')
    ->overlayImage('path/to/logo.png', 0.3, true)
    ->render('Hello World');
```

### Saving to File

Pass a file path as the second argument to `render` to save the QR code to a file.

```php
QrCode::render('Hello World', public_path('qrcodes/my-qr.svg'));
```

### Payloads

The package supports various payloads for common use cases.

**URL:**

```php
QrCode::url('https://example.com');
```

**Email:**

```php
QrCode::email('foo@bar.com', 'Subject', 'Body');
```

**SMS:**

```php
QrCode::sms('555-555-5555', 'Hello there!');
```

**WiFi:**

```php
QrCode::wifi([
    'encryption' => 'WPA/WPA2',
    'ssid' => 'MyNetwork',
    'password' => 'secret',
    'hidden' => false
]);
```

**Bitcoin:**

```php
QrCode::bitcoin('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 1.5);
```

**MeCard (Contact):**

```php
QrCode::mecard('John Doe', '1234567890', 'john@example.com');
```

**VCard (Contact):**

```php
QrCode::vcard('John Doe', [
    'ORG' => 'My Company',
    'TITLE' => 'Developer',
    'TEL' => '1234567890',
    'EMAIL' => 'john@example.com'
]);
```

**Calendar Event:**

```php
QrCode::calendar(
    'Meeting',
    '20231225T100000Z',
    '20231225T110000Z',
    ['location' => 'Office', 'description' => 'Discuss project']
);
```

**Facetime:**

```php
QrCode::facetime('1234567890', true); // true for audio-only
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
