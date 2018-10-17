<?php
/**
 * Created by PhpStorm.
 * User: Dmytryi
 * Date: 17.10.18
 * Time: 9:49
 */

namespace KonsolStudio\Test\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Configurable extends \Magento\Catalog\Block\Product\View
{

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;


    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     */
    protected $configurableProduct;

    /**
     * Configurable constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository ,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = [],
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
    ){

        $this->configurableType = $configurableType;

        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data);
    }

    /**
     * @return array
     */
    public function getCurrentOptions()
    {
        $currentOptions = array();
        $configurableProduct = $this->getConfigurableProduct();
        if(!$configurableProduct){
            return $currentOptions;
        }

        $configurableAttributes = $configurableProduct->getTypeInstance()->getConfigurableAttributes($configurableProduct);

        $product = $this->getProduct();

        foreach ($configurableAttributes as $attribute) {
            $eavAttribute = $attribute->getProductAttribute();
            $attributeCode = $eavAttribute->getAttributeCode();
            $currentOptions[$attributeCode] = $product->getData($attributeCode);
        }

        return $currentOptions;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     */
    public function getConfigurableProduct()
    {
        if(!$this->configurableProduct) {
            $product = $this->getProduct();
            if($product->getTypeId() == 'simple') {
                $parentId = $this->configurableType->getParentIdsByChild($product->getId());
                if(array($parentId)) {
                    $parentId = current($parentId);
                }
                try{
                    $this->configurableProduct = $this->productRepository->getById($parentId);
                } catch(\Exception $e) {
                    $this->configurableProduct = null;
                }
            } else {

            }
        }

        return $this->configurableProduct;
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}