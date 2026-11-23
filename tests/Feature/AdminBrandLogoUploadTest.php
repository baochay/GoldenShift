<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\ThuongHieu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminBrandLogoUploadTest extends TestCase
{
    protected NguoiDung $admin;

    /** @var array<int, string> */
    protected array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('thuong_hieu')) {
            Schema::create('thuong_hieu', function (Blueprint $table) {
                $table->increments('ma_thuong_hieu');
                $table->string('ten_thuong_hieu', 150);
                $table->string('ma_dinh_danh', 60)->nullable();
                $table->string('bieu_tuong', 255)->nullable();
                $table->string('anh_bia', 255)->nullable();
                $table->string('mo_ta_ngan', 255)->nullable();
                $table->text('gioi_thieu_chi_tiet')->nullable();
                $table->string('loai_hinh_am_thuc', 100)->nullable();
                $table->string('trang_thai', 30)->default('hoat_dong');
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('nguoi_dung')) {
            Schema::create('nguoi_dung', function (Blueprint $table) {
                $table->increments('ma_nguoi_dung');
                $table->string('ho_ten', 100);
                $table->string('email', 150)->unique();
                $table->string('so_dien_thoai', 20)->nullable();
                $table->string('mat_khau', 255)->default('');
                $table->string('vai_tro', 30)->default('ung_vien');
                $table->string('anh_dai_dien', 255)->nullable();
                $table->integer('ma_nha_hang_truc_thuoc')->nullable();
                $table->string('trang_thai', 30)->default('hoat_dong');
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        if (! Schema::hasTable('nha_hang')) {
            Schema::create('nha_hang', function (Blueprint $table) {
                $table->increments('ma_nha_hang');
                $table->integer('ma_thuong_hieu')->default(1);
                $table->integer('ma_quan_ly')->nullable();
                $table->string('ten_nha_hang', 200);
                $table->string('so_dien_thoai_lien_he', 20)->nullable();
                $table->string('email_chi_nhanh', 150)->nullable();
                $table->string('dia_chi_chi_tiet', 255)->default('');
                $table->string('phuong_xa', 100)->nullable();
                $table->string('quan_huyen', 100)->nullable();
                $table->string('tinh_thanh_pho', 100)->nullable();
                $table->string('trang_thai_hoat_dong', 30)->default('dang_hoat_dong');
                $table->timestamp('ngay_tao')->nullable();
                $table->timestamp('ngay_cap_nhat')->nullable();
            });
        }

        $this->admin = NguoiDung::firstOrCreate(
            ['email' => 'admin.brandtest@goldenshift.vn'],
            [
                'ho_ten' => 'Admin Test',
                'so_dien_thoai' => '0909111222',
                'mat_khau' => bcrypt('123456'),
                'vai_tro' => 'quan_tri_vien',
                'trang_thai' => 'hoat_dong',
            ]
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $relativePath) {
            if ($relativePath && file_exists(public_path($relativePath))) {
                @unlink(public_path($relativePath));
            }
        }

        parent::tearDown();
    }

    private function makeTinyPng(string $name = 'logo.png'): UploadedFile
    {
        $pngBinary = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()->createWithContent($name, $pngBinary);
    }

    public function test_admin_categories_renders_font_awesome_icon_without_broken_storage_img(): void
    {
        ThuongHieu::create([
            'ten_thuong_hieu' => 'Golden Hotpot Icon Test',
            'ma_dinh_danh' => 'golden-hotpot-icon-test',
            'bieu_tuong' => 'fa-fire-alt',
            'loai_hinh_am_thuc' => 'Lẩu Nướng',
            'trang_thai' => 'hoat_dong',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('fa-solid fa-fire-alt', false);
        $response->assertDontSee('storage/fa-fire-alt', false);
    }

    public function test_admin_can_create_brand_with_uploaded_logo_and_render_it(): void
    {
        $file = $this->makeTinyPng('golden_tea_logo.png');

        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.brands.store'), [
                'ten_thuong_hieu' => 'Golden Tea House',
                'ma_dinh_danh' => 'golden-tea-house',
                'loai_hinh_am_thuc' => 'Trà Sữa & Bánh',
                'mo_ta_ngan' => 'Thương hiệu trà sữa cao cấp',
                'trang_thai' => 'hoat_dong',
                'logo' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $brand = ThuongHieu::where('ma_dinh_danh', 'golden-tea-house')->firstOrFail();
        $this->assertNotNull($brand->bieu_tuong);
        $this->assertStringStartsWith('uploads/brands/', $brand->bieu_tuong);
        $this->assertFileExists(public_path($brand->bieu_tuong));
        $this->createdFiles[] = $brand->bieu_tuong;

        $page = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->get(route('admin.categories.index'));

        $page->assertStatus(200);
        $page->assertSee($brand->bieu_tuong, false);
    }

    public function test_admin_can_update_brand_logo_and_delete_previous_file(): void
    {
        $firstFile = $this->makeTinyPng('first_logo.png');

        $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.brands.store'), [
                'ten_thuong_hieu' => 'Golden Bakery',
                'ma_dinh_danh' => 'golden-bakery',
                'trang_thai' => 'hoat_dong',
                'logo' => $firstFile,
            ]);

        $brand = ThuongHieu::where('ma_dinh_danh', 'golden-bakery')->firstOrFail();
        $oldPath = $brand->bieu_tuong;
        $this->assertFileExists(public_path($oldPath));
        $this->createdFiles[] = $oldPath;

        $secondFile = $this->makeTinyPng('second_logo.png');

        $updateResponse = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.brands.update', $brand->ma_thuong_hieu), [
                'ten_thuong_hieu' => 'Golden Bakery Updated',
                'trang_thai' => 'hoat_dong',
                'logo' => $secondFile,
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $brand->refresh();
        $newPath = $brand->bieu_tuong;
        $this->createdFiles[] = $newPath;

        $this->assertNotEquals($oldPath, $newPath);
        $this->assertFileDoesNotExist(public_path($oldPath));
        $this->assertFileExists(public_path($newPath));
    }

    public function test_non_image_logo_upload_is_rejected(): void
    {
        $invalidFile = UploadedFile::fake()->create('script.txt', 10, 'text/plain');

        $response = $this->actingAs($this->admin)
            ->withSession(['user' => $this->admin])
            ->post(route('admin.brands.store'), [
                'ten_thuong_hieu' => 'Invalid Logo Brand',
                'ma_dinh_danh' => 'invalid-logo-brand',
                'trang_thai' => 'hoat_dong',
                'logo' => $invalidFile,
            ]);

        $response->assertSessionHasErrors('logo');
        $this->assertDatabaseMissing('thuong_hieu', [
            'ma_dinh_danh' => 'invalid-logo-brand',
        ]);
    }
}
