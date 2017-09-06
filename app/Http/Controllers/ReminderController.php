<?php

namespace App\Http\Controllers;

use App\Reminder;
use App\Nasabah;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Jobs\KreditReminder;
use Validator;

class ReminderController extends Controller
{
    function __construct()
    {
        $this->middleware('auth')->except(['test']);
    }

    public function validator($request)
    {
        return Validator::make($request, [
            'tanggal' => 'required|date',
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $template = \App\Template::find(1);
        if (request()->has('view')) {
            $nasabahs = Nasabah::whereHas('reminder_detail')->with('reminder_detail')->paginate(10)->withPath(request()->fullUrl());;
            // return $nasabahs;
            return view('reminder.index-nasabah', compact(['nasabahs', 'template']));
        }
        $reminders = Reminder::latest()->paginate(10);
        return view('reminder.index', compact(['reminders', 'template']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->validator(request()->all())->validate();
        $reminder = Reminder::where('tanggal', request('tanggal'))->get();
        $tanggal = Carbon::createFromFormat('Y-m-d', request('tanggal'))->formatLocalized('%d %B %Y');
        $template = \App\Template::find(1)->replaceDate($tanggal);
        return view('reminder.cek', compact(['reminder', 'tanggal', 'template']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();

        $reminder = Reminder::create(request(['tanggal']));

        // check dates on BMT database and send notification to registered nasabah
        foreach (\App\Cabang::all() as $cabang) {
            $reminder->con = $cabang->connection;
            dispatch(new KreditReminder($reminder));
        }

        return redirect('/reminder')->with('status', 'Reminder kredit berhasi diproses!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function show(Reminder $reminder)
    {
        return view('reminder.view', compact('reminder'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function edit(Reminder $reminder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reminder $reminder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reminder $reminder)
    {
        //
    }

    public function test(Nasabah $nasabah)
    {
        $tanggal = Carbon::createFromFormat('Y-m-d', '2017-07-31')->formatLocalized('%d %B %Y');
        $template = \App\Template::find(1)->replaceDate($tanggal);

        // GENERATE DATA
        $kredit= [
            'NO_REKENING' => '1.004.016099',
            'POKOK' => 12000,
            'BUNGA' => 10000,
            'NASABAH' => $nasabah->name,
            'ANGSURAN_KE' => 23,
        ];
        $data = [
            'kode' => 8,
            'data' => [
                'pesan' => $template,
                'kredit' => $kredit,
            ],
        ];

        foreach ($nasabah->device as $device) {
            dispatch(new \App\Jobs\SendFirebaseNotification('BMT Mobile App', 'Pengingat cicilan kredit', $data, $device->device_id));
        }
        
        return $data;

    }
}
