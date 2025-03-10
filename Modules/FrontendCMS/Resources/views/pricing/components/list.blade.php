<div class="row">
    <div class="col-lg-12">
        <table class="table Crm_table_active3">
            <thead>
                <tr>
                    <th scope="col">{{ __('common.sl') }}</th>
                    <th scope="col">{{ __('common.name') }}</th>
                    <th scope="col">{{ __('common.image') }}</th>
                    <th scope="col">{{__('frontendCms.plan_price')}}</th>
                    <th scope="col">{{__('frontendCms.team_size')}}</th>
                    <th scope="col">{{__('frontendCms.product_limit')}}</th>
                    <th scope="col">{{__('frontendCms.category_limit')}}</th>
                    <th scope="col">{{ __('common.status') }}</th>
                    <th scope="col">{{ __('common.action') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($PricingList as $key => $item)
                    <tr>
                        <td>{{getNumberTranslate($key + 1)}}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            <img style="width: 100px; height:100;" src="{{ showImage($item->image) }}" alt="">
                        </td>
                        <td>{{getNumberTranslate($item->plan_price) }}</td>
                        <td>{{getNumberTranslate( $item->team_size) }}</td>
                        <td>{{ getNumberTranslate($item->stock_limit) }}</td>
                        <td>{{ getNumberTranslate($item->category_limit) }}</td>
                        <td>
                            <label class="switch_toggle" for="checkbox{{ $item->id }}">
                                <input type="checkbox" id="checkbox{{ $item->id }}" {{$item->status?'checked':''}} class="statusChange" data-value="{{$item}}" value="{{$item->id}}" @if (permissionCheck('frontendcms.pricing.status'))
                                @endif>
                                <div class="slider round"></div>
                            </label>
                        </td>
                        <td>
                            <!-- shortby  -->
                            <div class="dropdown CRM_dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ __('common.select') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">

                                    @php
                                        $value = [
                                            "name" => $item->name,
                                            "monthly_cost" => $item->monthly_cost,
                                            "yearly_cost" => $item->yearly_cost,
                                            "team_size" => $item->team_size,
                                            "stock_limit" => $item->stock_limit,
                                            "category_limit" => $item->category_limit,
                                            "transaction_fee" => $item->transaction_fee,
                                            "best_for" => $item->best_for,
                                            "is_monthly" => $item->is_monthly,
                                            "is_yearly" => $item->is_yearly,
                                            "status" => $item->status,
                                            "is_featured" => $item->is_featured,
                                        ];
                                    @endphp
                                    <a data-value="{{ json_encode($value) }}" class="dropdown-item show_pricing">{{ __('common.show') }}</a>
                                    @if (permissionCheck('frontendcms.pricing.update'))
                                        <a data-value="{{ $item }}" class="dropdown-item edit_pricing">{{ __('common.edit') }}</a>
                                    @endif
                                    @if (permissionCheck('frontendcms.pricing.delete'))
                                        <a class="dropdown-item delete_pricing" data-id="{{$item->id}}">{{ __('common.delete') }}</a>
                                    @endif
                                </div>
                            </div>
                            <!-- shortby  -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
