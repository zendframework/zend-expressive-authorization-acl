# ACL Authorizations for Expressive

This component provides [Access Control List](https://en.wikipedia.org/wiki/Access_control_list)
(ACL) authorization abstraction for the [zend-expressive-authorization](https://github.com/zendframework/zend-expressive-authorization)
library.

ACLs are based around the idea of **resources** and **roles**:

- a **resource** is an object to which access is controlled;
- a **role** is an object that may request access to a resource.

Put simply, roles request access to resources. For example, if a parking
attendant requests access to a car, then the parking attendant is the requesting
role, and the car is the resource, since access to the car may not be granted to
everyone.

Through the specification and use of an ACL, an application may control how
roles are granted access to resources. For instance, in a web application a
*resource* can be a page, a portion of a view, a route, etc. A *role* can vary
based on the context in which the request is made: it could be the client
identity sent with an API request; whether the users is an _anonymous guest_ or a
_registered user_ of the site; etc.

## Configure an ACL system

You can provide your ACL definitions using a configuration file, as follows:

```php
// config/autoload/authorization.local.php
return [
    // ...
    'zend-expressive-authorization-acl' => [
        'roles' => [
            'administrator' => [],
            'editor'        => ['administrator'],
            'contributor'   => ['editor'],
        ],
        'resources' => [
            'admin.dashboard',
            'admin.posts',
            'admin.publish',
            'admin.settings'
        ],
        'allow' => [
            'administrator' => ['admin.settings'],
            'contributor' => [
                'admin.dashboard',
                'admin.posts',
            ],
            'editor' => [
                'admin.publish'
            ]
        ]
    ]
];
```

> We use this same example in the documentation of [zend-expressive-authorization-rbac](https://docs.zendframework.com/zend-expressive-authorization-rbac/v1/intro/#configure-an-rbac-system),
> so that you can compare and contrast the two systems.

The above configuration defines three roles for a blog web site:
*administrator*, *editor*, and *contributor*. The *administrator* has the
highest level of authorization (no parent).  A *contributor* has the permission
to create a post and manage the dashboard; its parent role is the
*administrator*.  Finally, an *editor* can only create or update a post; its
parent role is the *editor*.

> In ACL systems, parent roles inherit the permissions of their children.

Within zend-expressive-authorization-acl, *resources* are mapped to the *route
name* currently being requested.  By default, all resources are denied access,
unless otherwise stated. In our example, we allow the route `admin.settings` for
the *administrator*, the routes `admin.dashboard` and `admin.posts` for the
*contributor*, and the route `admin.publish` for the *editor*. Because the
*contributor* inherits permissions from *editor*, they will also have access to
the `admin.publish` route. Because the *administrator* inherits permissions from
*contributor*, they will have access to *all* routes.

You can also deny a resource using the `deny` key in the configuration file.
For instance, you can deny access to the route `admin.dashboard` by the
*administrator* by adding the following configuration in the previous example:

```php
return [
    // ...
    'zend-expressive-authorization-acl' => [
        // previous configuration array
        'deny' => [
            'administrator' => ['admin.dashboard']
        ]
    ]
]
```

The usage of `allow` and `deny` can help to configure complex permission
scenarios, including or excluding specific authorizations.

As noted earlier, zend-expressive-authorization-acl uses the current route name
to determine the resource. If you want to change the permissions type and the
logic for authorization, you will need to provide a custom implementation of
[`Zend\Expressive\Authorization\AuthorizationInterface`](https://github.com/zendframework/zend-expressive-authorization/blob/master/src/AuthorizationInterface.php).

> zend-expressive-authorization-acl uses [zend-permissions-acl](https://github.com/zendframework/zend-permissions-acl)
> to implement its ACL system. For more information, we suggest reading the
> [zend-acl documentation](https://docs.zendframework.com/zend-permissions-acl/).
