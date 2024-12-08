<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Imports\InvoiceImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class InvoiceImportTest extends TestCase
{

    protected $template;
    protected $rules;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            'formulas' => [
                ['new_column' => 'real_cost', 'formula' => 'weight * 2'],
            ],
            'validation_rules' => [
                [
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'ES',
                    'conditions' => [
                        [
                            'field' => 'weight',
                            'operator' => '>',
                            'value' => 10,
                            'highlight' => '000000',
                        ],
                    ],
                ],
                [
                    "field" => "weight",
                    'operator' => '>',
                    "value" => 10,
                    'highlight' => 'ffffff',
                ]
            ],
        ];
    }

    public function testImportProcessesCsvRowsCorrectly(): void
    {
         $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $import = new InvoiceImport($this->template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testImportProcessesCsvRowsWithoutMappingColumns(){

        $this->expectException(\Exception::class);

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [],
            'formulas' => [
                ['new_column' => 'real_cost', 'formula' => 'weight * 2'],
            ],
            'validation_rules' => [
                [
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'ES',
                    'conditions' => [
                        [
                            'field' => 'weight',
                            'operator' => '>',
                            'value' => 10,
                            'highlight' => '000000',
                        ],
                    ],
                ],
                [
                    "field" => "weight",
                    'operator' => '>',
                    "value" => 10,
                    'highlight' => 'ffffff',
                ]
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);
    }

    public function testImportReplaceTheSpaceWith_()
    {
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            "validation_rules" => [
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        // Verificar que las columnas se procesaron correctamente
        $this->assertCount(1, $processedData);


        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
        ], $processedData[0]);
    }

    public function testImportProcessesCsvRowsWithFormulas(): void
    {
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $import = new InvoiceImport($this->template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(1, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
        ], $processedData[0]);
    }

    public function testMarkColumnsDuplicated(){

          $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            'formulas' => [
                ['new_column' => 'real_cost', 'formula' => 'weight * 2'],
            ],
            'validation_rules' => [
                [
                    "duplicate_field" => "ship_id",
                    "row_highlight" => "ff0000",
                ]
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);


        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
            'duplicate' => true,
            'row_highlight' => 'ff0000'
        ], $processedData[1]);
    }


    public function testNotHasDuplicatedColumns(){

          $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            'formulas' => [
                ['new_column' => 'real_cost', 'formula' => 'weight * 2'],
            ],
            'validation_rules' => [
                [
                    "duplicate_field" => "ship_id",
                    "row_highlight" => "ff0000",
                ]
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        foreach ($processedData as $row) {
            $this->assertArrayNotHasKey('duplicate', $row, 'Row should not be marked as duplicate');
        }

        $this->assertCount(2, $processedData);
    }


    public function testImportOneColumnUsesOriginalKeyWhenMappingIsEmpty()
    {
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => '', // Debería usar la clave original 'devuelto'
            ],
            'validation_rules' => [],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(1, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'devuelto' => 'No', // Usa la clave original
        ], $processedData[0]);
    }


    public function testProcessWithColumnsWithEspecialCaracters(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 10,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                    'epep*+' => 'prueba',
                ],
            ]);

            $template = [
                'column_mappings' => [
                    'ship id' => 'ship_id',
                    'peso' => 'weight',
                    'peso 2' => 'weight_2',
                    'altura' => 'height',
                    'ancho' => 'width',
                    'largo' => 'length',
                    'coste' => 'cost',
                    'coste extra' => 'extra_cost',
                    'cliente' => 'client',
                    'direccion' => 'address',
                    'codigo postal' => 'postal_code',
                    'pais' => 'country',
                    'devuelto' => 'returned',
                    'epep*+' => 'special_column',
                ],
                'validation_rules' => [],
            ];

            $import = new InvoiceImport($template, []);

            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(1, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 10,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'special_column' => 'prueba',
            ], $processedData[0]);
    }

    public function testProcessHighlightCorrectly(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $import = new InvoiceImport($this->template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testNotHighlighBecauseNotHas(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            'formulas' => [
                ['new_column' => 'real_cost', 'formula' => 'weight * 2'],
            ],
            'validation_rules' => [
                [
                    'field' => 'country',
                    'operator' => '==',
                    'value' => 'ES',
                    'conditions' => [
                        [
                            'field' => 'weight',
                            'operator' => '>',
                            'value' => 1000,
                            'highlight' => 'ffffff',
                        ],
                    ],
                ],
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
        ], $processedData[1]);
    }

    public function testIfAddAplyCorrectionWorkCorrectly(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'action' => 'add', 'value' => 5],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 15,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);

    }

    public function testIfSubstractApplyCorrectionWorkCorrectly(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 10,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
                [
                    'ship id' => 'A002',
                    'peso' => 15,
                    'peso 2' => 10,
                    'altura' => 60,
                    'ancho' => 40,
                    'largo' => 25,
                    'coste' => 300,
                    'coste extra' => 70,
                    'cliente' => 'Cliente 2',
                    'direccion' => 'Calle 456',
                    'codigo postal' => '28002',
                    'pais' => 'US',
                    'devuelto' => 'No',
                ],
            ]);

            $rules = [
                [
                    'conditions' => [
                        ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                    ],
                    'corrections' => [
                        ['field' => 'weight', 'action' => 'subtract', 'value' => 5],
                    ],
                ],
            ];

            $import = new InvoiceImport($this->template, $rules);

            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(2, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 5,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost' => 10,
            ], $processedData[0]);

            $this->assertEquals([
                'ship_id' => 'A002',
                'weight' => 15,
                'weight_2' => 10,
                'height' => 60,
                'width' => 40,
                'length' => 25,
                'cost' => 300,
                'extra_cost' => 70,
                'client' => 'Cliente 2',
                'address' => 'Calle 456',
                'postal_code' => '28002',
                'country' => 'US',
                'returned' => 'No',
                'real_cost' => 30,
                'weight_highlight' => 'ffffff',
            ], $processedData[1]);
    }

    public function testIfUpdateApplyCorrectionWorkCorrectly(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'action' => 'update', 'value' => 22],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 22,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 44,
            'weight_highlight' => 'ffffff',
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testIfNotWorkinCorrectionWhyActionIsDiferent(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'action' => 'rest', 'value' => 22],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testCreateNewColumnWithUpdateCorrection(){
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'new_column' => 'real_weight', 'action' => 'update', 'value' => 22],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
            'real_weight' => 22,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testCreateNewColumnWithAddCorrection(){
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'new_column' => 'real_weight', 'action' => 'add', 'value' => 22],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
            'real_weight' => 32,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);

    }


    public function testCreateNewColumnWithSubstractCorrection(){
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 10,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 15,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    ['field' => 'weight', 'new_column' => 'real_weight', 'action' => 'subtract', 'value' => 22],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 10,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 20,
            'real_weight' => -12,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 15,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 30,
            'weight_highlight' => 'ffffff',
        ], $processedData[1]);
    }

    public function testCreateNewColumnWithComplexCorrection(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 1,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 2,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A003',
                'peso' => 3,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A004',
                'peso' => 5,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    [
                        'field' => 'weight',
                        'new_column' => 'real_cost_correct',
                        'action' => 'update',
                        'value' => [
                            ['min' => 1, 'max' => 2, 'value' => 4],
                            ['min' => 2, 'max' => 3, 'value' => 4.5],
                            ['min' => 3, 'max' => 5, 'value' => 5],
                            ['min' => 5, 'max' => 10, 'value' => 6],
                            ['min' => 10, 'max' => 20, 'value' => 8],
                            ['min' => 20, 'max' => 60, 'value' => 10],
                            ['min' => 60, 'step' => 5, 'value' => 0.5],
                        ],
                    ],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(4, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 1,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost_correct' => 4,
            'real_cost' => 2,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 2,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 4
        ], $processedData[1]);


        $this->assertEquals([
            'ship_id' => 'A003',
            'weight' => 3,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost' => 6
        ], $processedData[2]);

        $this->assertEquals([
            'ship_id' => 'A004',
            'weight' => 5,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost_correct' => 6,
            'real_cost' => 10
        ], $processedData[3]);
    }

    public function testCreateNewColumnWithComplexCorrection2(){
        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 1,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A002',
                'peso' => 2,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A003',
                'peso' => 3,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A004',
                'peso' => 5,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    [
                        'field' => 'weight',
                        'new_column' => 'real_cost_correct',
                        'action' => 'update',
                        'value' => [
                            ['min' => 1, 'max' => 2, 'value' => 4],
                            ['min' => 2, 'max' => 3, 'value' => 4.5],
                            ['min' => 3, 'max' => 5, 'value' => 5],
                            ['min' => 5, 'max' => 10, 'value' => 6],
                            ['min' => 10, 'max' => 20, 'value' => 8],
                            ['min' => 20, 'max' => 60, 'value' => 10],
                            ['min' => 60, 'step' => 5, 'value' => 0.5],
                        ],
                    ],
                ],
            ],
             [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'US'],
                ],
                'corrections' => [
                    [
                        'field' => 'weight',
                        'new_column' => 'real_cost_correct',
                        'action' => 'update',
                        'value' => [
                            ['min' => 1, 'max' => 2, 'value' => 11],
                            ['min' => 2, 'max' => 3, 'value' => 14.5],
                            ['min' => 3, 'max' => 5, 'value' => 15],
                            ['min' => 5, 'max' => 10, 'value' => 16],
                            ['min' => 10, 'max' => 20, 'value' => 18],
                            ['min' => 20, 'max' => 60, 'value' => 110],
                            ['min' => 60, 'step' => 5, 'value' => 0.7],
                        ],
                    ],
                ],
            ],
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(4, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 1,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost_correct' => 4,
            'real_cost' => 2,
        ], $processedData[0]);

        $this->assertEquals([
            'ship_id' => 'A002',
            'weight' => 2,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost_correct' => 14.5,
            'real_cost' => 4
        ], $processedData[1]);

        $this->assertEquals([
            'ship_id' => 'A003',
            'weight' => 3,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'US',
            'returned' => 'No',
            'real_cost_correct' => 15,
            'real_cost' => 6
        ], $processedData[2]);

        $this->assertEquals([
            'ship_id' => 'A004',
            'weight' => 5,
            'weight_2' => 10,
            'height' => 60,
            'width' => 40,
            'length' => 25,
            'cost' => 300,
            'extra_cost' => 70,
            'client' => 'Cliente 2',
            'address' => 'Calle 456',
            'postal_code' => '28002',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost_correct' => 6,
            'real_cost' => 10
        ], $processedData[3]);

    }


    public function testCreateNewColumnWithComplexCorrectionWorkStep(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 65,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
                [
                    'ship id' => 'A004',
                    'peso' => 70,
                    'peso 2' => 10,
                    'altura' => 60,
                    'ancho' => 40,
                    'largo' => 25,
                    'coste' => 300,
                    'coste extra' => 70,
                    'cliente' => 'Cliente 2',
                    'direccion' => 'Calle 456',
                    'codigo postal' => '28002',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
            ]);

            $rules = [
                [
                    'conditions' => [
                        ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                    ],
                    'corrections' => [
                        [
                            'field' => 'weight',
                            'new_column' => 'real_cost_correct',
                            'action' => 'update',
                            'value' => [
                                ['min' => 1, 'max' => 2, 'value' => 4],
                                ['min' => 2, 'max' => 3, 'value' => 4.5],
                                ['min' => 3, 'max' => 5, 'value' => 5],
                                ['min' => 5, 'max' => 10, 'value' => 6],
                                ['min' => 10, 'max' => 20, 'value' => 8],
                                ['min' => 20, 'max' => 60, 'value' => 10],
                                ['min' => 60, 'step' => 5, 'value' => 10, 'step_increment' => 0.5],
                            ],
                        ],
                    ],
                ]
            ];

            $import = new InvoiceImport($this->template, $rules);

            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(2, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 65,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost_correct' => 10.5,
                'real_cost' => 130,
                'weight_highlight' => 'ffffff',
            ], $processedData[0]);

            $this->assertEquals([
                'ship_id' => 'A004',
                'weight' => 70,
                'weight_2' => 10,
                'height' => 60,
                'width' => 40,
                'length' => 25,
                'cost' => 300,
                'extra_cost' => 70,
                'client' => 'Cliente 2',
                'address' => 'Calle 456',
                'postal_code' => '28002',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost_correct' => 11.0,
                'real_cost' => 140,
                'weight_highlight' => 'ffffff',
            ], $processedData[1]);
    }

    public function testHighlightCorrectAfterCorrection(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 5,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
                [
                    'ship id' => 'A004',
                    'peso' => 70,
                    'peso 2' => 10,
                    'altura' => 60,
                    'ancho' => 40,
                    'largo' => 25,
                    'coste' => 300,
                    'coste extra' => 70,
                    'cliente' => 'Cliente 2',
                    'direccion' => 'Calle 456',
                    'codigo postal' => '28002',
                    'pais' => 'US',
                    'devuelto' => 'No',
                ],
            ]);

            $rules = [
                [
                    'conditions' => [
                        ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                    ],
                    'corrections' => [
                        [
                            'field' => 'weight',
                            'action' => 'update',
                            'value' => 20,
                        ],
                    ],
                ]
            ];

            $import = new InvoiceImport($this->template, $rules);


            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(2, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 20,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost' => 40,
                'weight_highlight' => 'ffffff',
            ], $processedData[0]);
    }

    public function testCorrectionIfCellNotExist(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 5,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A004',
                'peso' => 70,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $rules = [
            [
                'conditions' => [
                    ['field' => 'country', 'operator' => '==', 'value' => 'ES'],
                ],
                'corrections' => [
                    [
                        'field' => 'weightttttt',
                        'action' => 'update',
                        'value' => 20,
                    ],
                ],
            ]
        ];

        $import = new InvoiceImport($this->template, $rules);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 5,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 10,
            'weightttttt' => 20,
        ], $processedData[0]);
    }

    public function testFormulasWorkCorrectly(){


        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 5,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A004',
                'peso' => 70,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            "formulas" => [
                [
                    "new_column" => "real_cost",
                    "formula" => "(cost + extra_cost)",
                ]
            ],
            "validation_rules" => [
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 5,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => 250,
        ], $processedData[0]);
    }

    public function testFormulasWithCeldNotExist(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 5,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
                [
                    'ship id' => 'A004',
                    'peso' => 70,
                    'peso 2' => 10,
                    'altura' => 60,
                    'ancho' => 40,
                    'largo' => 25,
                    'coste' => 300,
                    'coste extra' => 70,
                    'cliente' => 'Cliente 2',
                    'direccion' => 'Calle 456',
                    'codigo postal' => '28002',
                    'pais' => 'US',
                    'devuelto' => 'No',
                ],
            ]);

            $template = [
                'column_mappings' => [
                    'ship id' => 'ship_id',
                    'peso' => 'weight',
                    'peso 2' => 'weight_2',
                    'altura' => 'height',
                    'ancho' => 'width',
                    'largo' => 'length',
                    'coste' => 'cost',
                    'coste extra' => 'extra_cost',
                    'cliente' => 'client',
                    'direccion' => 'address',
                    'codigo postal' => 'postal_code',
                    'pais' => 'country',
                    'devuelto' => 'returned',
                ],
                "formulas" => [
                    [
                        "new_column" => "real_cost",
                        "formula" => "(cost + extra_cost_pepe)",
                    ]
                ],
                "validation_rules" => [
                ],
            ];

            $import = new InvoiceImport($template, []);

            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(2, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 5,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost' => null,
            ], $processedData[0]);
    }

    public function testFormulasWithFormulaNotValid(){

        $rows = new Collection([
            [
                'ship id' => 'A001',
                'peso' => 5,
                'peso 2' => 5,
                'altura' => 50,
                'ancho' => 30,
                'largo' => 20,
                'coste' => 200,
                'coste extra' => 50,
                'cliente' => 'Cliente 1',
                'direccion' => 'Calle 123',
                'codigo postal' => '28001',
                'pais' => 'ES',
                'devuelto' => 'No',
            ],
            [
                'ship id' => 'A004',
                'peso' => 70,
                'peso 2' => 10,
                'altura' => 60,
                'ancho' => 40,
                'largo' => 25,
                'coste' => 300,
                'coste extra' => 70,
                'cliente' => 'Cliente 2',
                'direccion' => 'Calle 456',
                'codigo postal' => '28002',
                'pais' => 'US',
                'devuelto' => 'No',
            ],
        ]);

        $template = [
            'column_mappings' => [
                'ship id' => 'ship_id',
                'peso' => 'weight',
                'peso 2' => 'weight_2',
                'altura' => 'height',
                'ancho' => 'width',
                'largo' => 'length',
                'coste' => 'cost',
                'coste extra' => 'extra_cost',
                'cliente' => 'client',
                'direccion' => 'address',
                'codigo postal' => 'postal_code',
                'pais' => 'country',
                'devuelto' => 'returned',
            ],
            "formulas" => [
                [
                    "new_column" => "real_cost",
                    "formula" => "(cost +_ extra_cost_pepe",
                ]
            ],
            "validation_rules" => [
            ],
        ];

        $import = new InvoiceImport($template, []);

        $import->collection($rows);

        $processedData = $import->processedData;

        $this->assertCount(2, $processedData);

        $this->assertEquals([
            'ship_id' => 'A001',
            'weight' => 5,
            'weight_2' => 5,
            'height' => 50,
            'width' => 30,
            'length' => 20,
            'cost' => 200,
            'extra_cost' => 50,
            'client' => 'Cliente 1',
            'address' => 'Calle 123',
            'postal_code' => '28001',
            'country' => 'ES',
            'returned' => 'No',
            'real_cost' => null,
        ], $processedData[0]);

    }

    public function testFormulaWithCodeInPhpErrorNewColumnInNull(){

            $rows = new Collection([
                [
                    'ship id' => 'A001',
                    'peso' => 5,
                    'peso 2' => 5,
                    'altura' => 50,
                    'ancho' => 30,
                    'largo' => 20,
                    'coste' => 200,
                    'coste extra' => 50,
                    'cliente' => 'Cliente 1',
                    'direccion' => 'Calle 123',
                    'codigo postal' => '28001',
                    'pais' => 'ES',
                    'devuelto' => 'No',
                ],
                [
                    'ship id' => 'A004',
                    'peso' => 70,
                    'peso 2' => 10,
                    'altura' => 60,
                    'ancho' => 40,
                    'largo' => 25,
                    'coste' => 300,
                    'coste extra' => 70,
                    'cliente' => 'Cliente 2',
                    'direccion' => 'Calle 456',
                    'codigo postal' => '28002',
                    'pais' => 'US',
                    'devuelto' => 'No',
                ],
            ]);

            $template = [
                'column_mappings' => [
                    'ship id' => 'ship_id',
                    'peso' => 'weight',
                    'peso 2' => 'weight_2',
                    'altura' => 'height',
                    'ancho' => 'width',
                    'largo' => 'length',
                    'coste' => 'cost',
                    'coste extra' => 'extra_cost',
                    'cliente' => 'client',
                    'direccion' => 'address',
                    'codigo postal' => 'postal_code',
                    'pais' => 'country',
                    'devuelto' => 'returned',
                ],
                "formulas" => [
                    [
                        "new_column" => "real_cost",
                        "formula" => "echo 'hola'",
                    ]
                ],
                "validation_rules" => [
                ],
            ];

            $import = new InvoiceImport($template, []);

            $import->collection($rows);

            $processedData = $import->processedData;

            $this->assertCount(2, $processedData);

            $this->assertEquals([
                'ship_id' => 'A001',
                'weight' => 5,
                'weight_2' => 5,
                'height' => 50,
                'width' => 30,
                'length' => 20,
                'cost' => 200,
                'extra_cost' => 50,
                'client' => 'Cliente 1',
                'address' => 'Calle 123',
                'postal_code' => '28001',
                'country' => 'ES',
                'returned' => 'No',
                'real_cost' => null,
            ], $processedData[0]);
    }

}
