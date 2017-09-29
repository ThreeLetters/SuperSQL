## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28268 Chars (28.3 MB)
* `SuperSQL_min.php` - 12497 Chars (12.5 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 17775 Chars (17.8 MB)

## Hashes

```
* SuperSQL.php - f5a8ac6f4c7f6f622a7520fa5c4ddc8f
* SuperSQL_min.php - 8cace3bc4d6e120edb0fa389febc9e1f
* SuperSQL_helper.php - 0c02d8d5fc75a6c1a534a91e524b4926
* SuperSQL_helper_min.php - 20dd20fe8c96741be6f159234c66d9f7
* SuperSQL_complete.php - 961087c769a95feb67983c227ccfc4e0
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.0173ms Average Time, Sum: 0.5189ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.001 | 0.0297 |
| 100 R Insert W Temp     | 0.0128 | 0.3854 |
| Select *                | 0.0005 | 0.0144 |
| Select * W Cast         | 0.0005 | 0.0136 |
| Select * W Cast W where | 0.0005 | 0.0152 |
| 1 Row Update            | 0.0011 | 0.0327 |
| Delete                  | 0.0009 | 0.026 |
