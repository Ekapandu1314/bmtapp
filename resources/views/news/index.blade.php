@extends ('layouts.master')

@section('breadcrumb')
	<ol class="breadcrumb">
      <li>BMT News</li>
    </ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-lg-8 col-lg-offset-2">
			@include('layouts.status')
			<div class="alert alert-info alert-with-icon" data-notify="container">
		        <button type="button" aria-hidden="true" class="close" data-dismiss="alert">×</button>
		        <i data-notify="icon" class="material-icons">info</i>
		        <span data-notify="message">Hanya 5 news terbaru yang akan ditampilkan di aplikasi</span>
		    </div>
			<div class="card">
	            <div class="card-header" data-background-color="bmt-green">
	            	<div class="row">
	            		<div class="col-xs-8">
	            			<h4 class="title">BMT News</h4>
			                <p class="category">Daftar news</p>
	            		</div>
	            		<div class="col-xs-4">
	            			<a href="/news/create" class="btn btn-round btn-just-icon pull-right" rel="tooltip" style="background: WhiteSmoke;;" title="Tambah news"><i class="material-icons" style="color: green;">add</i></a>
	            		</div>
	            	</div>
	            </div>
				<div class="card-content table-responsive">
					<table class="table">
						<thead>
							<tr class="text-success">
								<th>Nomer</th>
								<th>Judul</th>
								<th>Tanggal dibuat</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($news as $new)
								<tr>
									<td>{{ $news->toArray()['from']+$loop->index }}</td>
									<td>{{ $new->name }}</td>
									<td>{{ $new->created_at->format('j F Y') }}</td>
									<td class="td-actions text-right">
										<a href="/news/{{ $new->id }}/edit" type="button" rel="tooltip" title="Edit news" class="btn btn-primary btn-simple btn-xs">
											<i class="material-icons">edit</i>
										</a>
										<form action="/news/{{$new->id}}" method="POST">
											{{ csrf_field() }}
											{{ method_field('DELETE') }}
											<button type="submit" onclick="return confirm('Anda Yakin akan menghapus news?')" rel="tooltip" title="Hapus" class="btn btn-danger btn-simple btn-xs">
												<i class="material-icons">close</i>
											</button>
										</form>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('modal')
	@include('layouts.modals')
@endsection
