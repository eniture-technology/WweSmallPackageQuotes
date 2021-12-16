<?php
/**
 * @category   Shipping
 * @package    Eniture_WweSmallPackageQuotes
 * @author     Eniture Technology : <sales@eniture.com>
 * @website    http://eniture.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Eniture\WweSmallPackageQuotes\Setup;

use Eniture\WweSmallPackageQuotes\App\State;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Store\Model\Store;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var
     */
    private $attrNames;
    /**
     * @var
     */
    private $connection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ProductFactory
     */
    private $productLoader;
    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;
    /**
     * @var State
     */
    private $state;
    /**
     * @var ModuleDataSetupInterface
     */

    private $setup;
    /**
     * @var
     */
    private $eavSetup;

    /**
     * @var $haveTsAttributes
     */
    private $haveTsAttributes = false;


    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param State $state
     * @param CollectionFactory $collectionFactory
     * @param ProductFactory $productLoader
     * @param Config $eavConfig
     * @param ConfigInterface $resourceConfig
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        State $state,
        CollectionFactory $collectionFactory,
        ProductFactory $productLoader,
        Config $eavConfig,
        ConfigInterface $resourceConfig
    ) {
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->productLoader        = $productLoader;
        $this->eavConfig            = $eavConfig;
        $this->state                = $state;
        $this->resourceConfig       = $resourceConfig;
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->initVars($setup);
        $this->state->validateAreaCode();
        $this->attrNames();
        $this->renameOldAttributes();
        $this->addWweSmallAttributes();
        $this->createWweSmallWarehouseTable();
        $this->createEnitureModulesTable();
        $this->updateProductDimensionalAttr();
        $this->checkLILExistenceColumnForEnModules();
        $this->addOrderDetailColumn();
        $this->checkISLDColumnForWarehouse();
        $this->setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    private function initVars($setup)
    {
        $this->setup = $setup;
        $this->setup->startSetup();
        $this->eavSetup    = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->connection  = $this->setup->getConnection();
    }

    /**
     * Set Attribute names globally
     */
    private function attrNames()
    {
        $this->attrNames = [
            'length' => 'length',
            'width'  => 'width',
            'height' => 'height'
        ];
    }

    /**
     * Rename old attribute name
     */
    private function renameOldAttributes()
    {
        foreach ($this->attrNames as $attr) {
            $isExist = $this->eavConfig->getAttribute('catalog_product', 'wwe_'.$attr.'')->getAttributeId();
            if ($isExist != null) {
                $this->eavConfig->updateAttribute(
                    Product::ENTITY,
                    "wwe_$attr",
                    "attribute_code",
                    "en_$attr"
                );
            }
        }
    }

    /**
     * Checks existence of Order Detail Column and creates if not exists
     */
    private function addOrderDetailColumn()
    {

        $tableName = $this->setup->getTable('sales_order');
        if ($this->connection->isTableExists($tableName) == true) {
            if ($this->connection->tableColumnExists($tableName, 'order_detail_data') === false) {
                $this->connection->addColumn(
                    $tableName,
                    'order_detail_data',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => '',
                        'comment' => 'Order Detail Widget Data'
                    ]
                );
            }
        }
    }

    /**
     * add custom product attributes required for product settings
     */
    private function addWweSmallAttributes()
    {
        $count = 71;
        foreach ($this->attrNames as $attr) {
            if ($attr == 'length' || $attr == 'width' || $attr == 'height') {
                $isTsAttExists = $this->eavConfig
                    ->getAttribute('catalog_product', 'ts_dimensions_' . $attr . '')->getAttributeId();
                if ($isTsAttExists != null) {
                    $this->haveTsAttributes = true;
                    continue;
                }
            }
            $isExist = $this->eavConfig->getAttribute('catalog_product', 'en_'.$attr.'')->getAttributeId();
            if ($isExist == null) {
                $this->addProductAttribute(
                    'en_'.$attr,
                    Table::TYPE_DECIMAL,
                    ucfirst($attr),
                    'text',
                    '',
                    $count
                );
            }
            $count++;
        }


        $isEnDropshipExist = $this->eavConfig->getAttribute('catalog_product', 'en_dropship')->getAttributeId();

        if ($isEnDropshipExist == null) {
            $this->addProductAttribute(
                'en_dropship',
                'int',
                'Enable Drop Ship',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                74
            );
        }

        $isDropshipLocationExist = $this->eavConfig->getAttribute('catalog_product', 'en_dropship_location')->getAttributeId();

        if ($isDropshipLocationExist == null) {
            $this->addProductAttribute(
                'en_dropship_location',
                'int',
                'Drop Ship Location',
                'select',
                'Eniture\WweSmallPackageQuotes\Model\Source\DropshipOptions',
                75
            );
        } else {
            $this->eavSetup->updateAttribute(
                Product::ENTITY,
                "en_dropship_location",
                "source_model",
                "Eniture\WweSmallPackageQuotes\Model\Source\DropshipOptions"
            );
        }

        $isHazmatExist = $this->eavConfig->getAttribute('catalog_product', 'en_hazmat')->getAttributeId();

        if ($isHazmatExist == null) {
            $this->addProductAttribute(
                'en_hazmat',
                'int',
                'Hazardous Material',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                76
            );
        }

        $isInsurance = $this->eavConfig->getAttribute('catalog_product', 'en_insurance')->getAttributeId();

        if ($isInsurance == null) {
            $this->addProductAttribute(
                'en_insurance',
                'int',
                'Insure this item',
                'select',
                'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                77
            );
        }
    }

    /**
     * @param string $code
     * @param string $type
     * @param string $label
     * @param string $input
     * @param string $source
     * @param int $order
     * @return int
     */
    private function addProductAttribute($code, $type, $label, $input, $source, $order)
    {
        $attrArr = $this->eavSetup->addAttribute(
            Product::ENTITY,
            $code,
            [
                'group'            => 'Product Details',
                'type'             => $type,
                'backend'          => '',
                'frontend'         => '',
                'label'            => $label,
                'input'            => $input,
                'class'            => '',
                'source'           => $source,
                'global'           => ScopedAttributeInterface::SCOPE_STORE,
                'required'         => false,
                'visible_on_front' => false,
                'is_configurable'  => true,
                'sort_order'       => $order,
                'user_defined'     => true,
                'default'          => '0'
            ]
        );

        return $attrArr;
    }

    /**
     * create warehouse db table for module warehouse section
     */

    private function createWweSmallWarehouseTable()
    {
        $tableName = $this->setup->getTable('warehouse');
        if ($this->connection->isTableExists($tableName) != true) {
            $table = $this->connection
                ->newTable($tableName)
                ->addColumn('warehouse_id', Table::TYPE_INTEGER, null, [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                ], 'Id')
                ->addColumn('city', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'city')
                ->addColumn('state', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'state')
                ->addColumn('zip', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'zip')
                ->addColumn('country', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'country')
                ->addColumn('location', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'location')
                ->addColumn('nickname', Table::TYPE_TEXT, 30, [
                    'nullable'  => false,
                ], 'nickname')
                ->addColumn(
                    'in_store',
                    Table::TYPE_TEXT,
                    512,
                    [],
                    'in store pick up'
                )
                ->addColumn(
                    'local_delivery',
                    Table::TYPE_TEXT,
                    512,
                    [],
                    'local delivery'
                );
            $this->connection->createTable($table);
        }
    }

    /**
     * create EnitureModules db table for active modules
     */
    private function createEnitureModulesTable()
    {
        $moduleTableName = $this->setup->getTable('enituremodules');
        // Check if the table already exists
        if ($this->connection->isTableExists($moduleTableName) != true) {
            $table = $this->connection
                ->newTable($moduleTableName)
                ->addColumn('module_id', Table::TYPE_INTEGER, null, [
                    'identity'  => true,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                ], 'id')
                ->addColumn('module_name', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'module_name')
                ->addColumn('module_script', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'module_script')
                ->addColumn('dropship_field_name', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'dropship_field_name')
                ->addColumn('dropship_source', Table::TYPE_TEXT, 200, [
                    'nullable'  => false,
                ], 'dropship_source');

            $this->connection->createTable($table);
        }

        $newModuleName  = 'ENWweSmpkg';
        $scriptName     = 'Eniture_WweSmallPackageQuotes';
        $isNewModuleExist  = $this->connection->fetchOne(
            "SELECT count(*) AS count FROM ".$moduleTableName." WHERE module_name = '".$newModuleName."'"
        );
        if ($isNewModuleExist == 0) {
            $insertDataArr = [
                'module_name' => $newModuleName,
                'module_script' => $scriptName,
                'dropship_field_name' => 'en_dropship_location',
                'dropship_source' => 'Eniture\WweSmallPackageQuotes\Model\Source\DropshipOptions'
            ];
            $this->connection->insert($moduleTableName, $insertDataArr);
        }
    }

    /**
     *
     */
    private function updateProductDimensionalAttr()
    {
        $lengthChange = $widthChange = $heightChange = false;

        if ($this->haveTsAttributes) {
            $productCollection = $this->collectionFactory->create()->addAttributeToSelect('*');
            foreach ($productCollection as $_product) {
                $product = $this->productLoader->create()->load($_product->getEntityId());

                $savedEnLength  = $_product->getData('en_length');
                $savedEnWidth   = $_product->getData('en_width');
                $savedEnHeight  = $_product->getData('en_height');

                if (isset($savedEnLength) && $savedEnLength) {
                    $product->setData('ts_dimensions_length', $savedEnLength)
                        ->getResource()->saveAttribute($product, 'ts_dimensions_length');
                    $lengthChange = true;
                }

                if (isset($savedEnWidth) && $savedEnWidth) {
                    $product->setData('ts_dimensions_width', $savedEnWidth)
                        ->getResource()->saveAttribute($product, 'ts_dimensions_width');
                    $widthChange = true;
                }

                if (isset($savedEnHeight) && $savedEnHeight) {
                    $product->setData('ts_dimensions_height', $savedEnHeight)
                        ->getResource()->saveAttribute($product, 'ts_dimensions_height');
                    $heightChange = true;
                }
            }
        }

        $this->removeEnitureAttr($lengthChange, $widthChange, $heightChange);
    }

    /**
     * @param bool $lengthChange
     * @param bool $widthChange
     * @param bool $heightChange
     */
    private function removeEnitureAttr($lengthChange, $widthChange, $heightChange)
    {
        if ($lengthChange == true) {
            $this->eavSetup->removeAttribute(Product::ENTITY, 'en_length');
        }

        if ($widthChange == true) {
            $this->eavSetup->removeAttribute(Product::ENTITY, 'en_width');
        }

        if ($heightChange == true) {
            $this->eavSetup->removeAttribute(Product::ENTITY, 'en_height');
        }
    }

    /**
     * Add column to eniture modules table
     */
    private function checkLILExistenceColumnForEnModules()
    {
        $tableName = $this->setup->getTable('enituremodules');

        if ($this->connection->isTableExists($tableName) == true) {
            if ($this->connection->tableColumnExists($tableName, 'is_ltl') === false) {
                $this->connection->addColumn($tableName, 'is_ltl', [
                    'type'      => Table::TYPE_BOOLEAN,
                    'comment'   => 'module type'
                ]);
            }
        }

        $this->connection->update($tableName, ['is_ltl' => 0], "module_name = 'ENWweSmpkg'");
    }

    /**
     * @param type $path
     * @param type $value
     */
    function saveConfigurations($path, $value)
    {
        $this->resourceConfig->saveConfig(
            $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }

    /**
     * Add column to eniture modules table
     */
    private function checkISLDColumnForWarehouse()
    {
        $tableName = $this->setup->getTable('warehouse');
        if ($this->connection->isTableExists($tableName) == true) {
            if ($this->connection->tableColumnExists($tableName, 'in_store') === false &&
                $this->connection->tableColumnExists($tableName, 'local_delivery') === false) {
                $columns = [
                    'in_store' => [
                        'type'      => Table::TYPE_TEXT,
                        'comment'   => 'in store pick up'
                    ],
                    'local_delivery' => [
                        'type'      => Table::TYPE_TEXT,
                        'comment'   => 'local delivery'
                    ]

                ];
                foreach ($columns as $name => $definition) {
                    $this->connection->addColumn($tableName, $name, $definition);
                }
            }
        }
    }
}
