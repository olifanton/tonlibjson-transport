PHP tonlibjson transport
---

![Based on TON](https://img.shields.io/badge/Based%20on-TON-blue)

---
[💬 En chat](https://t.me/olifanton_en) | [💬 Ру чат](https://t.me/olifanton_ru)

---
⚠️⚠️⚠️ This package in active development!
---

Asynchronous implementation of tonlibjson transport via PHP FFI.

To work in non-blocking mode the following can be used: 

- [OpenSwoole](https://openswoole.com/) extension;
- [ReactPHP](https://docs.react-async.com/) package;
- [AMPHP](https://amphp.org/) package (WIP).

## Documentation

__WIP__

## Tests

```bash
composer run test
```

## Contributing

Please make sure to read the [Olifanton contribution guide](https://github.com/olifanton/.github/blob/main/profile/CONTRIBUTING.md) before making a pull request.

### Setup environment

Prepare your environment for development.

Note that the instructions describe working on *nix systems (Linux and possibly macOS),
development on Windows may be difficult and will not be covered in these instructions.

You'll need:

1. Minimum PHP version: 8.1;
2. OpenSwoole extension.

### Fork repository

Make a repository fork in your GitHub account.

### Clone your repository

```bash
git clone git@github.com:<YOUR_GITHUB_NAME>/tonlibjson-transport.git
cd tonlibjson-transport
```

### Create a `feature/` (or `hotfix/`) branch

```bash
git branch feature/<FEATURE_NAME>
git checkout feature/<FEATURE_NAME>
```

### Create pull request

After implementing your new feature (or hotfix) in your local branch, you should
commit and push changes to your fork repository. After that you can create a pull-request.

---

# License

MIT
