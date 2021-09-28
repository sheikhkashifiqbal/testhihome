<?php

#app/Http/Admin/Controllers/ShopProductController.php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopAttributeGroup;
use App\Models\ShopBrand;
use App\Models\ShopCategory;
use App\Models\ShopLanguage;
use App\Models\ShopProduct;
use App\Models\ShopProductAttribute;
use App\Models\ShopProductBuild;
use App\Models\ShopProductDescription;
use App\Models\ShopProductGroup;
use App\Models\ShopProductImage;
use App\Models\ShopVendor;
use App\Models\AdminStore as Store;
use App\Models\AdminStoreShopCategory;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use App\Models\ShopUser;
use App\Admin\Services\ProductService;
use App\Modules\Notification\Services\NotificationService;

class ShopProductController extends Controller {

    public $languages, $types, $kinds, $virtuals, $attributeGroup;

    public function __construct() {
        $this->languages = ShopLanguage::getList();
        $this->attributeGroup = ShopAttributeGroup::getList();
        $this->types = [
            SC_PRODUCT_NORMAL => trans('product.types.normal'),
            SC_PRODUCT_NEW => trans('product.types.new'),
            SC_PRODUCT_HOT => trans('product.types.hot'),
        ];
        $this->kinds = [
            SC_PRODUCT_SINGLE => trans('product.kinds.single'),
            SC_PRODUCT_BUILD => trans('product.kinds.build'),
            SC_PRODUCT_GROUP => trans('product.kinds.group'),
        ];
        $this->virtuals = [
            SC_VIRTUAL_PHYSICAL => trans('product.virtuals.physical'),
            SC_VIRTUAL_DOWNLOAD => trans('product.virtuals.download'),
            SC_VIRTUAL_ONLY_VIEW => trans('product.virtuals.only_view'),
            SC_VIRTUAL_SERVICE => trans('product.virtuals.service'),
        ];
    }

    public function index() {
        //$status=Request->status;
        $active = request('active', 1);

        $data = [
            'title' => trans('product.admin.list'),
            'icon' => 'fa fa-indent'
        ];

        $listTh = $this->returnListTH();


        $sort_order = request('sort_order') ?? 'id__desc';
        $keyword = request('keyword') ?? '';
        $category_id = request('category_id') ?? '';
        $store_id = request('stroe_id') ?? '';
        $arrSort = $this->returnSortArray();

        $products = ShopProduct::withRelations($active)->withSort($sort_order);
        //dd($products->get())w
        if ($keyword) {
            $products = $products->withSearchKeyword($keyword);
        }

        if ($category_id) {
            $products = $products->withCategoryFilter($category_id);
        }

        if ($store_id) {
            $products = $products->withStoreFilter($store_id);
        }
      //  dd($products->get());
        $dataTmp = $products->paginate(20);

        $data['listTh'] = $listTh;
        $data['dataTr'] = $this->returnTableTD($dataTmp);
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
        $data['result_items'] = trans('product.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);

        $data['menu_left'] = $this->returnMenuLeft();

        $data['menu_sort'] = $this->returnMenuSort($sort_order);

        $data['script_sort'] = $this->returnScriptSort($active);

        $data['menu_search'] = $this->returnMenuSearch($keyword, $active);

        $data['url_delete_item'] = route('admin_product.delete');

        return view('admin.screen.list')
                        ->with($data);
    }

    private function returnListTH()
    {
        $listTh = [
            'check_row' => '',
            'id' => trans('product.id'),
            'image' => trans('product.image'),
            'name' => trans('product.english_name'),
            'arabic_name' => trans('product.arabic_name'),
            'seller' => trans('product.seller'),
            'category' => trans('product.category'),
        ];
        if (sc_config('product_cost')) {
            $listTh['cost'] = trans('product.cost');
        }
        if (sc_config('product_price')) {
            $listTh['price'] = trans('product.final_price');
        }
        if (sc_config('product_type')) {
            $listTh['type'] = trans('product.type');
        }
        if (sc_config('product_kind')) {
            $listTh['kind'] = trans('product.kind');
        }
        if (sc_config('product_virtual')) {
            $listTh['virtual'] = trans('product.virtual');
        }
        $listTh['status'] = trans('product.status');
        $listTh['stock_status'] = trans('product.stock_status');
        $listTh['action'] = trans('product.admin.action');
        return $listTh;
    }

    private function returnTableTD($dataTmp)
    {
      $dataTr = [];
      foreach ($dataTmp as $key => $row) {
          $kind = $this->kinds[$row['kind']] ?? $row['kind'];
          if ($row['kind'] == SC_PRODUCT_BUILD) {
              $kind = '<span class="label label-success">' . $kind . '</span>';
          } elseif ($row['kind'] == SC_PRODUCT_GROUP) {
              $kind = '<span class="label label-danger">' . $kind . '</span>';
          }
          $type = $this->types[$row['type']] ?? $row['type'];
          if ($row['type'] == SC_PRODUCT_NEW) {
              $type = '<span class="label label-success">' . $type . '</span>';
          } elseif ($row['type'] == SC_PRODUCT_HOT) {
              $type = '<span class="label label-danger">' . $type . '</span>';
          }

          $eng_desc = $row->description('en')->first();
          $ar_desc = $row->description('ar')->first();

          $dataMap = [
              'check_row' => '<input type="checkbox" class="grid-row-checkbox" data-id="' . $row['id'] . '">',
              'id' => $row['id'],
              'image' => cdn_image_render($row->image, '50', '50'),
              'name' => ($eng_desc) ? $eng_desc->name : "",
              'arabic_name' => ($ar_desc) ? $ar_desc->name : "",
              'seller' => $row->seller['title'],
              'category' => implode('; ', $row->categories->pluck('name')->toArray()),
          ];

          if (sc_config('product_cost')) {
              $dataMap['cost'] = $row['cost'];
          }
          if (sc_config('product_price')) {
              //$dataMap['price'] = $row['price'];
              $dataMap['price'] = ProductService::calculateFinalPrice($row);
          }
          if (sc_config('product_type')) {
              $dataMap['type'] = $type;
          }
          if (sc_config('product_kind')) {
              $dataMap['kind'] = $kind;
          }
          if (sc_config('product_virtual')) {
              $dataMap['virtual'] = $this->virtuals[$row['virtual']] ?? $row['virtual'];
          }
          $dataMap['status'] = $row['status'] ? '<span class="label label-success">ON</span>' : '<span class="label label-danger">OFF</span>';
          $dataMap['stock_status'] = $row['out_of_stock'] ? '<span class="label label-danger">Out Of Stock</span>' : '<span class="label label-success">In Stock</span>';
          $dataMap['action'] = '
          <a href="' . route('admin_product.edit', ['id' => $row['id']]) . '">
          <span title="' . trans('product.admin.edit') . '" type="button" class="btn btn-flat btn-primary">
          <i class="fa fa-edit"></i>
          </span>
          </a>&nbsp;

          <span onclick="deleteItem(' . $row['id'] . ');"  title="' . trans('admin.delete') . '" class="btn btn-flat btn-danger">
          <i class="fa fa-trash"></i>
          </span>';
          $dataTr[] = $dataMap;
      }
      return $dataTr;
    }

    private function returnSortArray()
    {
      return [
          'id__desc' => trans('product.admin.sort_order.id_desc'),
          'id__asc' => trans('product.admin.sort_order.id_asc'),
          'name__desc' => trans('product.admin.sort_order.name_desc'),
          'name__asc' => trans('product.admin.sort_order.name_asc'),
      ];
    }

    private function returnMenuSearch($keyword, $active)
    {
        return '<form action="' . route('admin_product.index') . '" id="button_search">
                   <div onclick="$(this).submit();" class="btn-group pull-right">
                           <a class="btn btn-flat btn-primary" title="Refresh">
                              <i class="fa  fa-search"></i><span class="hidden-xs"> ' . trans('admin.search') . '</span>
                           </a>
                   </div>
                   <div class="btn-group pull-right">
                         <div class="form-group">
                           <input type="text" name="keyword" class="form-control" placeholder="' . trans('product.admin.search_place') . '" value="' . $keyword . '">
                           <input type="hidden" value="' . $active . '" name="active" />
                         </div>
                   </div>
                   <div class="btn-group pull-right">
                         <div class="form-group">
                         <select name="category_id" class="form-control">
                         <optgroup label="Select a category"><option value="">Select a category</option></optgroup>
                         ' . Store::getStoresWithCategories() . '
                       </select>
                         </div>
                   </div>
                    <div class="btn-group pull-right">
                         <div class="form-group">
                         <select name="stroe_id" class="form-control">
                         ' . Store::getStoresHtml() . '
                       </select>
                         </div>
                   </div>
                </form>';
    }

    private function returnScriptSort($active)
    {
        return "$('#button_sort').click(function(event) {
                var url = '" . route('admin_product.index') . "?active=" . $active . "&sort_order='+$('#order_sort option:selected').val();
                $.pjax({url: url, container: '#pjax-container'})
              });";
    }

    private function returnMenuSort($sort_order)
    {
      $arrSort = $this->returnSortArray();
      $optionSort = '';
      foreach ($arrSort as $key => $status) {
          $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
      }
      return '<div class="btn-group pull-left">
                      <div class="form-group">
                         <select class="form-control" id="order_sort">
                          ' . $optionSort . '
                         </select>
                       </div>
                     </div>

                     <div class="btn-group pull-left">
                         <a class="btn btn-flat btn-primary" title="Sort" id="button_sort">
                            <i class="fa fa-sort-amount-asc"></i><span class="hidden-xs"> ' . trans('admin.sort') . '</span>
                         </a>
                     </div>';
    }

    private function returnMenuLeft()
    {
      return '<div class="pull-left">
                  <button type="button" class="btn btn-default grid-select-all"><i class="fa fa-square-o"></i></button> &nbsp;

                  <a class="btn   btn-flat btn-danger grid-trash" title="Delete"><i class="fa fa-trash-o"></i><span class="hidden-xs"> ' . trans('admin.delete') . '</span></a> &nbsp;

                  <a class="btn   btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('admin.refresh') . '</span></a> &nbsp;</div>
                  ';
    }

    /**
     * Form create new order in admin
     * @return [type] [description]
     */
    public function create() {
        $listProductSingle = (new ShopProduct)->getListSigle();

        // html select product group
        $htmlSelectGroup = '<div class="select-product">';
        $htmlSelectGroup .= '<table width="100%"><tr><td width="80%"><select class="form-control productInGroup select2" data-placeholder="' . trans('product.admin.select_product_in_group') . '" style="width: 100%;" name="productInGroup[]" >';
        $htmlSelectGroup .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectGroup .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectGroup .= '</select></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductInGroup"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectGroup .= '</div>';
        //End select product group
        // html select product build
        $htmlSelectBuild = '<div class="select-product">';
        $htmlSelectBuild .= '<table width="100%"><tr><td width="70%"><select class="form-control productInGroup select2" data-placeholder="' . trans('product.admin.select_product_in_build') . '" style="width: 100%;" name="productBuild[]" >';
        $htmlSelectBuild .= '';
        foreach ($listProductSingle as $k => $v) {
            $htmlSelectBuild .= '<option value="' . $k . '">' . $v['name'] . '</option>';
        }
        $htmlSelectBuild .= '</select></td><td style="width:100px"><input class="form-control"  type="number" name="productBuildQty[]" value="1" min=1></td><td><span title="Remove" class="btn btn-flat btn-danger removeproductBuild"><i class="fa fa-times"></i></span></td></tr></table>';
        $htmlSelectBuild .= '</div>';
        //end select product build
        // html select attribute
        $htmlProductAtrribute = '<tr><td><br><input type="text" name="attribute[attribute_group][]" value="attribute_value" class="form-control input-sm" placeholder="' . trans('product.admin.add_attribute_place') . '" /></td><td><br><span title="Remove" class="btn btn-flat btn-sm btn-danger removeAttribute"><i class="fa fa-times"></i></span></td></tr>';
        //end select attribute
        // html add more images
        $htmlMoreImage = '<div class="input-group"><input type="text" id="id_sub_image" name="sub_image[]" value="image_value" class="form-control input-sm sub_image" placeholder=""  /><span class="input-group-btn"><a data-input="id_sub_image" data-preview="preview_sub_image" data-type="product" class="btn btn-sm btn-primary lfm"><i class="fa fa-picture-o"></i> Choose</a></span></div><div id="preview_sub_image" class="img_holder"></div>';
        //end add more images


        $stores_with_categories = Store::getStoresWithCategories($brand_id = null, $html = 0);
        $data = [
            'title' => trans('product.admin.add_new_title'),
            'sub_title' => '',
            'title_description' => trans('product.admin.add_new_des'),
            'icon' => 'fa fa-plus',
            'languages' => $this->languages,
            'categories' => (new ShopCategory)->getTreeCategories(),
            'brands' => (new ShopBrand)->getList(),
            'vendors' => (new ShopVendor)->getList(),
            'types' => $this->types,
            'virtuals' => $this->virtuals,
            'kinds' => $this->kinds,
            'attributeGroup' => $this->attributeGroup,
            'htmlSelectGroup' => $htmlSelectGroup,
            'htmlSelectBuild' => $htmlSelectBuild,
            'listProductSingle' => $listProductSingle,
            'htmlProductAtrribute' => $htmlProductAtrribute,
            'htmlMoreImage' => $htmlMoreImage,
            'stores_with_categories' => $stores_with_categories,
        ];

        return view('admin.screen.product_add')
                        ->with($data);
    }

    /**
     * Post create new order in admin
     * @return [type] [description]
     */
    public function postCreate() {

        $data = request()->all();
        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias']) ? $data['alias'] : $data['descriptions'][$langFirst]['name'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);
        $data['kind'] = $data['kind'] ?? 0;
        switch ($data['kind']) {
            case SC_PRODUCT_SINGLE: // product single
                $arrValidation = [
                    //'kind' => 'required',
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:100',
                    //'descriptions.*.keyword' => 'nullable|string|max:100',
                    'descriptions.*.description' => 'nullable|string|max:100',
                    'descriptions.*.content' => 'required|string',
                    'category' => 'required',
                        //'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku',
                        //'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias|string|max:100',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                    'descriptions.*.content.required' => trans('validation.required', ['attribute' => trans('product.content')]),
                    'category.required' => trans('validation.required', ['attribute' => trans('product.category')]),
                        //'sku.regex' => trans('product.sku_validate'),
                        //'alias.regex' => trans('product.alias_validate'),
                ];
                break;

            case SC_PRODUCT_BUILD: //product build
                $arrValidation = [
                    'kind' => 'required',
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:100',
                    'descriptions.*.keyword' => 'nullable|string|max:100',
                    'descriptions.*.description' => 'nullable|string|max:100',
                    'category' => 'required',
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku',
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias|string|max:100',
                    'productBuild' => 'required',
                    'productBuildQty' => 'required',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                    'category.required' => trans('validation.required', ['attribute' => trans('product.category')]),
                    'sku.regex' => trans('product.sku_validate'),
                    'alias.regex' => trans('product.alias_validate'),
                ];
                break;

            case SC_PRODUCT_GROUP: //product group
                $arrValidation = [
                    'kind' => 'required',
                    'productInGroup' => 'required',
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku',
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias|string|max:100',
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                    'sku.regex' => trans('product.sku_validate'),
                    'alias.regex' => trans('product.alias_validate'),
                ];
                break;

            default:
                $arrValidation = [
                    'kind' => 'required',
                ];
                break;
        }

        $validator = Validator::make($data, $arrValidation, $arrMsg ?? []);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput($data);
        }

        $category = $data['category'] ?? [];
        $attribute = $data['attribute'] ?? [];
        $descriptions = $data['descriptions'];
        $productInGroup = $data['productInGroup'] ?? [];
        $productBuild = $data['productBuild'] ?? [];
        $productBuildQty = $data['productBuildQty'] ?? [];
        $subImages = $data['sub_image'] ?? [];
        $data['image'] = $data['image'] ?? '';
        $data['image'] = str_replace(env('APP_URL'), "", $data['image']);
        $dataInsert = [
            'brand_id' => $data['brand_id'] ?? 0,
            'vendor_id' => $data['vendor_id'] ?? 0,
            'price' => $data['price'] ?? 0,
            'sku' => $data['sku'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'type' => $data['type'] ?? SC_PRODUCT_NORMAL,
            'kind' => $data['kind'] ?? SC_PRODUCT_SINGLE,
            'alias' => $data['alias'],
            'virtual' => $data['virtual'] ?? SC_VIRTUAL_PHYSICAL,
            'date_available' => !empty($data['date_available']) ? $data['date_available'] : null,
            'image' => $data['image'] ?? '',
            'status' => (!empty($data['status']) ? 1 : 0),
            'sort' => (int) $data['sort'],
            'gluten_free' => (!empty($data['gluten_free']) ? 1 : 0),
            'weight' => $data['weight'] ?? 0,
        ];
        //insert product
        $product = ShopProduct::create($dataInsert);

        //Promoton price
        if (isset($data['price_promotion']) && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrPromotion['price_promotion'] = $data['price_promotion'];
            $arrPromotion['date_start'] = $data['price_promotion_start'] ? $data['price_promotion_start'] : null;
            $arrPromotion['date_end'] = $data['price_promotion_end'] ? $data['price_promotion_end'] : null;
            $product->promotionPrice()->create($arrPromotion);
        }

        //Insert category
        if ($category && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->categories()->attach($category);
        }
        //Insert group
        if ($productInGroup && $data['kind'] == SC_PRODUCT_GROUP) {
            $arrDataGroup = [];
            foreach ($productInGroup as $pID) {
                if ((int) $pID) {
                    $arrDataGroup[$pID] = new ShopProductGroup(['product_id' => $pID]);
                }
            }
            $product->groups()->saveMany($arrDataGroup);
        }

        //Insert Build
        if ($productBuild && $data['kind'] == SC_PRODUCT_BUILD) {
            $arrDataBuild = [];
            foreach ($productBuild as $key => $pID) {
                if ((int) $pID) {
                    $arrDataBuild[$pID] = new ShopProductBuild(['product_id' => $pID, 'quantity' => $productBuildQty[$key]]);
                }
            }
            $product->builds()->saveMany($arrDataBuild);
        }

        //Insert attribute
        if ($attribute && $data['kind'] == SC_PRODUCT_SINGLE) {
            $arrDataAtt = [];
            foreach ($attribute as $group => $rowGroup) {
                if (count($rowGroup)) {
                    foreach ($rowGroup as $key => $nameAtt) {
                        if ($nameAtt) {
                            $arrDataAtt[] = new ShopProductAttribute(['name' => $nameAtt, 'attribute_group_id' => $group]);
                        }
                    }
                }
            }
            $product->attributes()->saveMany($arrDataAtt);
        }

        //Insert description
        $dataDes = [];
        $languages = $this->languages;
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'product_id' => $product->id,
                'lang' => $code,
                'name' => $descriptions[$code]['name'],
                'keyword' => $descriptions[$code]['keyword'] ?? null,
                'description' => $descriptions[$code]['description'],
                'content' => $descriptions[$code]['content'] ?? '',
                'ingredients' => $descriptions[$code]['ingredients'] ?? null,
                'time_to_prepare' => $descriptions[$code]['time_to_prepare'] ?? null,
                'allergies' => $descriptions[$code]['allergies'] ?? null,
            ];
        }

        ShopProductDescription::insert($dataDes);

        //Insert sub mages
        if ($subImages && in_array($data['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrSubImages = [];
            foreach ($subImages as $key => $image) {
                if ($image) {
                    $image = str_replace(env('APP_URL'), "", $image);
                    $arrSubImages[] = new ShopProductImage(['image' => $image]);
                }
            }
            $product->images()->saveMany($arrSubImages);
        }

        return redirect()->route('admin_product.index')->with('success', trans('product.admin.create_success'));
    }

    /**
     * Form edit
     */
    public function edit($id) {
        $product = ShopProduct::find($id);

        if ($product === null) {
            return 'no data';
        }

        $listProductSingle = (new ShopProduct)->getListSigle();


        // html select attribute
        $htmlProductAtrribute = '<tr><td><br><input type="text" name="attribute[attribute_group][]" value="attribute_value" class="form-control input-sm" placeholder="' . trans('product.admin.add_attribute_place') . '" /></td><td><br><span title="Remove" class="btn btn-flat btn-sm btn-danger removeAttribute"><i class="fa fa-times"></i></span></td></tr>';
        //end select attribute

        $data = [
            'title' => trans('product.admin.edit'),
            'sub_title' => '',
            'title_description' => '',
            'icon' => 'fa fa-pencil-square-o',
            'languages' => $this->languages,
            'product' => $product,
            'categories' => (new ShopCategory)->getTreeCategories(),
            'brands' => (new ShopBrand)->getList(),
            'vendors' => (new ShopVendor)->getList(),
            'types' => $this->types,
            'virtuals' => $this->virtuals,
            'kinds' => $this->kinds,
            'attributeGroup' => $this->attributeGroup,
            'htmlSelectGroup' => '',
            'htmlSelectBuild' => '',
            'listProductSingle' => $listProductSingle,
            'htmlProductAtrribute' => $htmlProductAtrribute,
        ];
        return view('admin.screen.product_edit')
                        ->with($data);
    }

    /**
     * update status
     */
    public function postEdit($id) {
        $product = ShopProduct::with('descriptions')->find($id);
        $data = request()->all();
        //dd($data['/']);
        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias']) ? $data['alias'] : $data['descriptions'][$langFirst]['name'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        switch ($product['kind']) {
            case SC_PRODUCT_SINGLE: // product single
                $arrValidation = [
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    //'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                    //'descriptions.*.content' => 'required|string',
                    'category' => 'required',
                        //'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku,' . $product->id . ',id',
                        //'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias,' . $product->id . ',id|string|max:100',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                    'descriptions.*.content.required' => trans('validation.required', ['attribute' => trans('product.content')]),
                    'descriptions.*.description.required' => trans('validation.required', ['attribute' => trans('product.description')]),
                    'category.required' => trans('validation.required', ['attribute' => trans('product.category')]),
                        //'sku.regex' => trans('product.sku_validate'),
                        //'alias.regex' => trans('product.alias_validate'),
                ];
                break;
            case SC_PRODUCT_BUILD: //product build
                $arrValidation = [
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                    'category' => 'required',
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku,' . $product->id . ',id',
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias,' . $product->id . ',id|string|max:100',
                    'productBuild' => 'required',
                    'productBuildQty' => 'required',
                ];
                $arrMsg = [
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                    'category.required' => trans('validation.required', ['attribute' => trans('product.category')]),
                    'sku.regex' => trans('product.sku_validate'),
                    'alias.regex' => trans('product.alias_validate'),
                ];
                break;

            case SC_PRODUCT_GROUP: //product group
                $arrValidation = [
                    'sku' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,sku,' . $product->id . ',id',
                    'alias' => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|unique:shop_product,alias,' . $product->id . ',id|string|max:100',
                    'productInGroup' => 'required',
                    'sort' => 'numeric|min:0',
                    'descriptions.*.name' => 'required|string|max:200',
                    'descriptions.*.keyword' => 'nullable|string|max:200',
                    'descriptions.*.description' => 'nullable|string|max:300',
                ];
                $arrMsg = [
                    'sku.regex' => trans('product.sku_validate'),
                    'alias.regex' => trans('product.alias_validate'),
                    'descriptions.*.name.required' => trans('validation.required', ['attribute' => trans('product.name')]),
                ];
                break;

            default:
                break;
        }

        $validator = Validator::make($data, $arrValidation, $arrMsg ?? []);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput($data);
        }
        //Edit

        $category = $data['category'] ?? [];
        $attribute = $data['attribute'] ?? [];
        $productInGroup = $data['productInGroup'] ?? [];
        $productBuild = $data['productBuild'] ?? [];
        $productBuildQty = $data['productBuildQty'] ?? [];
        $subImages = $data['sub_image'] ?? [];

        $data['image'] = cdn_image_upload_from_storage($data['image'], 'products');

        $dataUpdate = [
            'image' => $data['image'] ?? '',
            'brand_id' => $data['brand_id'] ?? 0,
            'vendor_id' => $data['vendor_id'] ?? 0,
            'price' => $data['price'] ?? 0,
            'prepration_time' => $data['prepration_time'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'type' => $data['type'] ?? SC_PRODUCT_NORMAL,
            'virtual' => $data['virtual'] ?? SC_VIRTUAL_PHYSICAL,
            'date_available' => !empty($data['date_available']) ? $data['date_available'] : null,
            'sku' => $data['sku'] ?? 0,
            'alias' => $data['alias'],
            'status' => (!empty($data['status']) ? 1 : 0),
            'out_of_stock' => (!empty($data['out_of_stock']) ? 1 : 0),
            'sort' => (int) $data['sort'],
            'serve_count' => $data['serve_count'] ?? 0,
                //  'gluten_free' => (!empty($data['gluten_free']) ? 1 : 0),
                //   'weight' =>  $data['weight'] ?? 0,
        ];

        $product->update($dataUpdate);

        //store in notification table
        NotificationService::productUpdateNotification($product->store_id, $product);

        $active = $product->status;
        $seller_user = ShopUser::where('role', 'seller')
                ->where('store_id', $product->store_id)
                ->first();
        // dd($seller_user);
        $cofig['to'] = $email = $seller_user->email;

        if ($active == 1) {
            $msg = 'Your product is active now';
        } else {
            $msg = 'Your product is inactive';
        }
        $data = array('title' => '【HiHOME.app】 Product Status', 'content' => $msg);
        Mail::queue(new SendMail('mail.product_inactive', $data, $cofig));
        if ($seller_user && !empty($seller_user->device_udid)) {
            send_notification_to_seller(
                    $msg, $seller_user->device_udid, null, ['content' => ''], null, null, 'Seller Account:'
            );
        }
        //}
        //Promoton price
        $product->promotionPrice()->delete();
        if (isset($data['price_promotion']) && in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $arrPromotion['price_promotion'] = $data['price_promotion'];
            $arrPromotion['date_start'] = $data['price_promotion_start'] ? $data['price_promotion_start'] : null;
            $arrPromotion['date_end'] = $data['price_promotion_end'] ? $data['price_promotion_end'] : null;
            $product->promotionPrice()->create($arrPromotion);
        }

        $product->descriptions()->delete();


            $dataDes = [];
            foreach (request()->all()['descriptions'] as $code => $row) {
                //dd($data['descriptions'] );
                $dataDes[] = [
                    'product_id' => $id,
                    'lang' => $code,
                    'name' => $row['name'],
                    'keyword' => $row['keyword'] ?? null,
                    'description' => $row['description']?? '',
                    'content' => $row['content'] ?? '',
                    'ingredients' => $row['ingredients'] ,
                    //'time_to_prepare' => $descriptions[$code]['time_to_prepare'] ?? null,
                    'allergies' => $row['allergies'] ?? null,
                ];
            }

           // dd( $dataDes);
            //if(!empty($dataDes))
            ShopProductDescription::insert($dataDes);

        //Update category
        if (in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->categories()->detach();
            if (count($category)) {
                $product->categories()->attach($category);
            }
        }


        foreach($category as $cat){
          $store_cateory_id=AdminStoreShopCategory::where('store_id',$product->store_id)->where('category_id', $cat)->first();
          //dd($store_cateory_id);
          if(!$store_cateory_id){
              AdminStoreShopCategory::insert(['store_id'=>$product->store_id,'category_id'=>$cat]);
          }
        }//

        //Update group
        if ($product['kind'] == SC_PRODUCT_GROUP) {
            $product->groups()->delete();
            if (count($productInGroup)) {
                $arrDataGroup = [];
                foreach ($productInGroup as $pID) {
                    if ((int) $pID) {
                        $arrDataGroup[$pID] = new ShopProductGroup(['product_id' => $pID]);
                    }
                }
                $product->groups()->saveMany($arrDataGroup);
            }
        }

        //Update Build
        if ($product['kind'] == SC_PRODUCT_BUILD) {
            $product->builds()->delete();
            if (count($productBuild)) {
                $arrDataBuild = [];
                foreach ($productBuild as $key => $pID) {
                    if ((int) $pID) {
                        $arrDataBuild[$pID] = new ShopProductBuild(['product_id' => $pID, 'quantity' => $productBuildQty[$key]]);
                    }
                }
                $product->builds()->saveMany($arrDataBuild);
            }
        }

        //Update attribute
        if ($product['kind'] == SC_PRODUCT_SINGLE) {
            $product->attributes()->delete();
            if (count($attribute)) {
                $arrDataAtt = [];
                foreach ($attribute as $group => $rowGroup) {
                    if (count($rowGroup)) {
                        foreach ($rowGroup as $key => $nameAtt) {
                            if ($nameAtt) {
                                $arrDataAtt[] = new ShopProductAttribute(['name' => $nameAtt, 'attribute_group_id' => $group]);
                            }
                        }
                    }
                }
                $product->attributes()->saveMany($arrDataAtt);
            }
        }

        //Update sub mages
        if ($subImages && in_array($product['kind'], [SC_PRODUCT_SINGLE, SC_PRODUCT_BUILD])) {
            $product->images()->delete();
            $arrSubImages = [];
            foreach ($subImages as $key => $image) {
                if ($image) {
                    $image = cdn_image_upload_from_storage($image, 'products');
                    $arrSubImages[] = new ShopProductImage(['image' => $image]);
                }
            }
            $product->images()->saveMany($arrSubImages);
        }

        //
        return redirect()->route('admin_product.index')->with('success', trans('product.admin.edit_success'));
    }

    /*
      Delete list Item
      Need mothod destroy to boot deleting in model
     */

    public function deleteList() {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            $arrCantDelete = [];
            foreach ($arrID as $key => $id) {
                if (ShopProductBuild::where('product_id', $id)->first() || ShopProductGroup::where('product_id', $id)->first()) {
                    $arrCantDelete[] = $id;
                }
            }
            if (count($arrCantDelete)) {
                return response()->json(['error' => 1, 'msg' => trans('product.admin.cant_remove_child') . ': ' . json_encode($arrCantDelete)]);
            } else {
                ShopProduct::destroy($arrID);
                return response()->json(['error' => 0, 'msg' => '']);
            }
        }
    }

}
