@extends('layouts.app')

@section('title', 'Performance Management Test')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Performance Management System Test</h1>
            <p>This page shows all available performance management features and their status.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Available Features</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Dashboard:</strong> 
                                    <a href="{{url('/performance')}}" class="btn btn-sm btn-primary">Access</a>
                                </li>
                                <li class="list-group-item">
                                    <strong>Report Builder:</strong> 
                                    @can('performance.template.create')
                                        <a href="{{route('performance.templates.create')}}" class="btn btn-sm btn-success">Access</a>
                                    @else
                                        <span class="text-danger">No Permission</span>
                                    @endcan
                                </li>
                                <li class="list-group-item">
                                    <strong>KPIs:</strong> 
                                    @can('performance.kpi.view_all')
                                        <a href="{{route('performance.kpis.index')}}" class="btn btn-sm btn-success">Access</a>
                                    @else
                                        <span class="text-danger">No Permission</span>
                                    @endcan
                                </li>
                                <li class="list-group-item">
                                    <strong>Goals:</strong> 
                                    @can('performance.goals.view_all')
                                        <a href="{{route('performance.goals.index')}}" class="btn btn-sm btn-success">Access</a>
                                    @else
                                        <span class="text-danger">No Permission</span>
                                    @endcan
                                </li>
                                <li class="list-group-item">
                                    <strong>Alerts:</strong> 
                                    @can('performance.dashboard.admin')
                                        <a href="{{route('performance.alerts.index')}}" class="btn btn-sm btn-success">Access</a>
                                    @else
                                        <span class="text-danger">No Permission</span>
                                    @endcan
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Data Status</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>KPIs:</strong> 
                                    <span class="badge">{{ \App\Models\Performance\PerformanceKPI::count() }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Goals:</strong> 
                                    <span class="badge">{{ \App\Models\Performance\PerformanceGoal::count() }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Alerts:</strong> 
                                    <span class="badge">{{ \App\Models\Performance\PerformanceAlert::count() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">User Permissions</h3>
                        </div>
                        <div class="panel-body">
                            <p><strong>Current User:</strong> {{ auth()->user()->name }}</p>
                            <p><strong>Performance Permissions:</strong></p>
                            <ul>
                                @foreach(auth()->user()->getAllPermissions()->where('name', 'like', 'performance.%') as $permission)
                                    <li>{{ $permission->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
