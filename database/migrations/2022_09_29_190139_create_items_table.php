<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            //

            $table->id();

            $table->string('itemCode');
            $table->string('description');
            $table->string('desc2');
            $table->string('furtherDescription');
            $table->string('itemGroupId');
            $table->string('itemGroupTitle');
            $table->string('itemTypeId')->nullable();
            $table->string('itemTypeTitle')->nullable();
            $table->timestamps(); 

            
            // "assemblyCost": 0,
            // "leadTime": "",
            // "stockControl": true,
            // "hasSerialNo": false,
            // "hasBatchNo": false,
            // "taxTypeId": null,
            // "taxTypeTitle": null,
            // "note": "",
            // "salesUOM": "UNIT",
            // "purchaseUOM": "UNIT",
            // "reportUOM": "UNIT",
            // "isCalcBonusPoint": true,
            // "hasPromoter": false,
            // "itemBrandId": null,
            // "itemBrandTitle": null,
            // "discontinued": false,
            // "autoUOMConversion": true,
            // "baseUOM": "UNIT",
            // "backOrderControl": true,
            // "purchaseTaxTypeId": null,
            // "purchaseTaxTypeTitle": null,
            // "itemClassId": null,
            // "itemClassTitle": null,
            // "itemCategoryId": null,
            // "itemCategoryTitle": null,
            // "isSalesItem": true,
            // "isPurchaseItem": true,
            // "isPOSItem": true,
            // "isRawMaterialItem": true,
            // "isFinishGoodsItem": true,
            // "lastUpdate": 1,
            // "isActived": true,
            // "acGuid": null,
            // "docKey": 0,
            // "autoKey": 0,
            // "readyToAC": true,
            // "isDeleted": false,
            // "deleterUserId": null,
            // "deletionTime": null,
            // "lastModificationTime": "2022-09-15T00:02:09.2915291",
            // "lastModifierUserId": 2,
            // "creationTime": "2022-08-05T09:45:29.8641517",
            // "creatorUserId": 132,
            // "id": "74a26482-cc32-41fe-a7ad-f3f2d24e3037"
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
}
