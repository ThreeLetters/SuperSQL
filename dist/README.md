## Files

* `SuperSQL.php` - Main file
* `SuperSQL_min.php`
* `SuperSQL_helper.php` - Helper functions
* `SuperSQL_helper_min.php`

### Sizes

* `SuperSQL.php` - 28810 Chars (28.8 MB)
* `SuperSQL_min.php` - 12747 Chars (12.7 MB)
* `SuperSQL_helper.php` - 11234 Chars (11.2 MB)
* `SuperSQL_helper_min.php` - 5558 Chars (5.6 MB)
* `SuperSQL_complete_min.php` - 18025 Chars (18 MB)

## Hashes

```
* SuperSQL.php - ef68d9f5a5223d8dc8a1e5c9710fee2a
* SuperSQL_min.php - cb59dc4145f4f928ab0420dbf12cf63a
* SuperSQL_helper.php - c28eb974df4b6b59cfd1703b93ba5ad5
* SuperSQL_helper_min.php - 434f45d06501865c04386f32874637bf
* SuperSQL_complete.php - 12d192dc93e6bb86d0bc32caf98f2de1
```

## Performance

Profiled on PHP v7.1.4, 30 loops


0.023ms Average Time, Sum: 0.6896ms

### Specifics

| Name                    |  Avg   |  Sum   |
|-------------------------|--------|--------|
| 1 Row Insert            | 0.0013 | 0.038 |
| 100 R Insert W Temp     | 0.0173 | 0.5191 |
| Select *                | 0.0007 | 0.02 |
| Select * W Cast         | 0.0006 | 0.0178 |
| Select * W Cast W where | 0.0007 | 0.0201 |
| 1 Row Update            | 0.0014 | 0.0413 |
| Delete                  | 0.001 | 0.0309 |
