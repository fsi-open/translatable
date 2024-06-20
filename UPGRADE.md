# Upgrade to 1.1

## BC Break: Using doctrine/orm >= 3.0 requires manually initializing proxied entity before returning any WebFile property

If you are using `doctrine/orm` version 3.0 or newer, which implies using Symfony\Component\VarExporter\LazyGhostTrait
instead of legacy Doctrine proxies, you need to manually initialize the entity proxy before returning any
translatable property from translatable entity. This is due to the changes in the way the proxy based on lazy ghost is
initialized.

This action is not always required in order to use the component, but it is necessary when you want to access a
translatable property of an entity that is being lazy loaded through some relation. However, it's perfectly valid and
safe to initialize the proxy before accessing any translatable property in any translatable entity.

Here is an example of how to do it:

```php
use FSi\Component\Translatable\Integration\Doctrine\ORM\ProxyTrait;

class TranslatableEntity {
    use ProxyTrait;

    private ?string $title;

    ...

    public function getTitle(): ?string
    {
        $this->initializeProxy();

        return $this->title;
    }

    ...
}
```

Another way is to eagerly load the translatable entity with through the relation
(see https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/reference/working-with-objects.html#by-eager-loading).
