/**
 * Created by jrpro_000 on 8/1/2016.
 */

$(document).on('click','.got-it',function(cname, cvalue){
    localStorage.setItem("gotIt", "true");
});

$(document).ready(function(){
   if((localStorage.getItem("gotIt")) =="true"){
       $('.weAreWorking').addClass('hide');
   }
});

$(document).on('click','.add-to-favorite',function(){
    var addedClass = $(this).hasClass( "added" );
    if(addedClass == true)
    {
        var property_id = $(this).attr('property_id');
        var user_id = $(this).attr('user_id');
        var key = $(this).attr('key');
        $.ajax({
            type: "POST",
            url: apiPath.concat("favourite/property/delete"),
            data:{
                propertyId:property_id,userId:user_id
            },
            headers: {
                Authorization: key
            },
            success: function(response) {
         $('.add-to-favorite').closest('a').removeClass('added');
      },
            error: function () {
         $('.popup-opener').closest('li').addClass('popup-holder');
      }

        });
    }
    else {
        var property_id = $(this).attr('property_id');
        var key = $(this).attr('key');
        $.ajax({
            type: "POST",
            url: apiPath.concat("favourite/property"),
            data: {
                propertyId: property_id
            },
            headers: {
                Authorization: key
            },
            success: function(response) {
                $('.add-to-favorite').closest('a').addClass('added');
            }
        })
    }
});


$(document).on('change', '#society', function(){
    var society_id = $(this).val();
    if(society_id !="") {
        $('#blocks').closest('li').addClass('loading');
        $.ajax({
            url: apiPath.concat("society/blocks"),
            data: {
                society_id: society_id
            },
            success: function (response) {
                $('#blocks').empty();
                $('#blocks').append($('<option>').text('select a block').attr('value', ''));
                $.each(response.data.blocks, function (i, block) {
                    $('#blocks').append($('<option>').text(block.name).attr('value', block.id));
                });
                $('#blocks').closest('li').removeClass('loading');
                $('#blocks').trigger('loaded');
            }
        })
    }
    else
    {
        $('#blocks').empty();
        $('#blocks').append($('<option>').text('All block').attr('value',''));
        $('#blocks').closest('li').removeClass('loading');
    }
});

