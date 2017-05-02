# TWStreet

## Installation

```
composer require hsquare/tw-street
```

## Install in Laravel 5

### Setup

Add ServiceProvider to the providers array in `app/config/app.php`.

```
Hsquare\TWStreet\TWStreetServiceProvider::class,
```

### Description

Get data of counties, districts, and streets from postal web site of Taiwan and export excel file.

### Usage

```
php artisan hsquare:twstreet
```

### Output Path

```
storage/excel/exports
```
