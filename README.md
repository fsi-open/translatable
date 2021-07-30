# Translatable

This components serves to provide a way of creating translatable objects for multiple
languages. There are two main concepts governing this idea - a translatable and a translation.
A translatable is an object whose data is stored in a collection of translation objects,
each per a separate locale. Each of these has a separate configuration object, where all the
necessary information about the relation between the two is stored. These are `TranslatableConfiguration`
and `TranslationConfiguration` and can be retrieved via `ConfigurationResolver`.

The lifecycle of a translatable object is handled by a set of dedicated classes in the `Entity` directory
and these are `TranslationLoader`, `TranslationUpdater` and `TranslationCleaner`. They will:

- set the current locale for the translatable object,
- load data from a translation (if one exists),
- create a new translation or update the existing one,
- remove empty translation objects.

However they will not work on their own and need to be hooked up to whatever storage mechanism you
are using (ORM, ODM etc.) through a subscriber(s). Currently there is out-of-the-box integration only for
Doctrine / Symfony combination.

## What can be a translatable field?

When it comes to storage, mapping/configuration for all of the below should be included in the
translation entity files, not the translatable.

### By default

- scalar values (string, integer, float),
- objects (though their storage will probably need to be handled through some integration),
- `WebFile` objects from `fsi/files` component will work and be persisted/removed along with the translation entity,

### with Doctrine

- embeddables (also nested), although embeddables themselves cannot have translations due to not having an identifier,
- one-to-one relations,
- collection relations,

## Example entities

Let us consider an example of a translatable `Article` entity with `ArticleTranslation` translation:

```php

declare(strict_types=1);

namespace Tests\Entity;

use DateTimeImmutable;

class Article
{
    private ?int $id = null;
    private ?string $locale = null;
    private ?DateTimeImmutable $publicationDate = null;
    private ?string $title = null;
    private ?string $description = null;

    // getters and setters will probably required for whatever
    // mechanism you use for modyfing the object, though are not
    // required by the component itself
}

declare(strict_types=1);

namespace Tests\Entity;

class ArticleTranslation
{
    private ?int $id = null;
    private ?string $locale = null;
    private ?string $title = null;
    private ?string $description = null;
    private ?Article $article;

    // getters and setters are not required
}
```

As you can see, they are almost a mirror of each other, aside for the `$publicationDate` field
in the translatable and `$article` field in the translation. That is because `$publicationDate`
is not translatable field (it is stored directly in the translatable object) and `$article` serves
as a way to bind the translation to the translatable. Both fields have the `locale` field: the
translatable stores the current locale and the translation has the locale it was created for.

In order for the component to recognize these objects as the translatable-translation duo, you
will need to define their configurations. When using Symfony, this can be achieved as simply as
this:

```yaml
fsi_translatable:
    entities:
        Tests\Entity\Article:
            localeField: locale # this can be skipped for a default value of locale
            fields: [title, description]
            translation:
                localeField: locale # also can be skipped
                class: Tests\FSi\ArticleTranslation
                relationField: article

```

**IMPORTANT**
- Both objects need to have the same fields present. It is not possible to map translatable fields to different fields in the translation entity.
- Translation objects **cannot** have required constructor arguments.

If you want to create your configuration manually, you will need to provide the `ConfigurationResolver` with
a collection of `TranslatableConfiguration` objects with the necessary data.

## Usage (Doctrine + Symfony)

Unless you load your bundles and configuartion directly in the Kernel class, you will need to load
the Translatable bundle in the `config/bundles.php`:

```php
return [
    // Doctrine and Symfony bundles
    FSi\Component\Translatable\Integration\Symfony\TranslatableBundle::class => ['all' => true]
];
```

and then add a `config/packages/fsi_translatable.yaml` file:

```yaml
fsi_translatable:
    entities:
        # configuration for specific entities, see above for an example
```

You can of course load the configuration manually through PHP, but XML configuration is not supported at the moment.

After that the component will mostly behave automatically. When creating a new translatable object with
any of the translatable fields filled, the current locale (via `LocaleProvider` object) will be fetched, a new
instance of translation will be created and then the relevant contents of the translatable object will be copied
into it. On subsequent loading of the translatable object, the data will be loaded back from the stored translation.
Any modifications to the translatable fields in the translatable object will update the existing translation
automatically. Should the locale provided by the `LocaleProvider` be different, a new translation will be created.

If for some reason you need to fetch single/all translation objects directly, you can do so via the `TranslationProvider`.

If a translatable object is removed, all the translations will be cleared throught Doctrine's entity manager (via `TranslationManager`),
so anysubscribers listening on the translation entity's lifecycle events will be fired as well.

**IMPORTANT**
- If you have translatable collection fields, you **need** to initialize them in the translation object's constructor.

### Locale fetching and persisting

The `LocaleProvider` implementation for Symfony will try to fetch the locale from three sources:

1. It will check the for a persisted locale (more on that later).
2. Failing to find one, it will fetch a current `Request` object from a `RequestStack` and retrieve the locale from that.
3. Should there be no current `Request` (this will be the case for console commands and some test environments), it will return the default locale from the `FrameworkBundle`.

If you want to manually set the locale that is returned from the `LocaleProvider`, calling the `LocaleProvider::setLocale()`
method will persist it in the session until it is cleared or you will manually call the `LocaleProvider::clearSavedLocale()`.
