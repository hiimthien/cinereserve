<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\MovieReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class MovieReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $reviewsPool = [
            // 🌟 Đánh giá 10 sao (Kiệt tác / Trải nghiệm rạp đỉnh cao)
            [
                'rating' => 10,
                'comment' => 'Kỹ xảo và âm thanh xem rạp IMAX Laser quá mãn nhãn! Từng khung hình đều đạt độ chi tiết kinh ngạc, đoạn cao trào nổi hết da gà. Xứng đáng là siêu phẩm chiếu rạp của năm!',
                'likes' => 48,
            ],
            [
                'rating' => 10,
                'comment' => 'Diễn xuất của dàn cast đỉnh cao thật sự. Đặc biệt là ánh mắt và biểu cảm nhân vật chính ở 20 phút cuối làm cả rạp lặng người. 10/10 không có điểm nào để chê!',
                'likes' => 56,
            ],
            [
                'rating' => 10,
                'comment' => 'Một kiệt tác điện ảnh thật sự! Đồ họa vượt xa kỳ vọng, các pha combat nghẹt thở. Ai là fan cứng thì chắc chắn không thể bỏ qua được!',
                'likes' => 62,
            ],
            [
                'rating' => 10,
                'comment' => 'Suất chiếu sớm hôm nay đông kín rạp! May mà đặt vé trước trên CineReserve chọn được hàng ghế E VIP ngay chính giữa màn hình. Trải nghiệm xem phim 5 sao!',
                'likes' => 41,
            ],
            [
                'rating' => 10,
                'comment' => 'Âm thanh vòm Dolby Atmos rung chuyển cả phòng chiếu, cảm giác như chính mình đang hòa mình vào trận chiến. Phim xem rạp mới thấy hết cái hay!',
                'likes' => 37,
            ],
            [
                'rating' => 10,
                'comment' => 'Cốt truyện xuất sắc, lồng ghép nhiều tầng ý nghĩa nhân văn sâu sắc. Đoạn after-credit làm cả rạp vỗ tay rần rần. Chắc chắn sẽ đi xem lại lần 2!',
                'likes' => 51,
            ],

            // ⭐ Đánh giá 9 sao (Rất hay / Cực kỳ đáng tiền vé)
            [
                'rating' => 9,
                'comment' => 'Phim cuốn từ đầu đến cuối không rời mắt được phút nào. Cốt truyện logic, âm nhạc hào hùng nâng tầm cảm xúc. Khuyên thật lòng mọi người nên chọn phòng chiếu lớn để trải nghiệm trọn vẹn.',
                'likes' => 33,
            ],
            [
                'rating' => 9,
                'comment' => 'Đi xem cùng bạn gái bằng ghế Sweetbox rất thoải mái và riêng tư. Phim hài hước duyên dáng mà cũng đọng lại nhiều ý nghĩa về gia đình. Sẽ rủ thêm bạn bè đi xem!',
                'likes' => 29,
            ],
            [
                'rating' => 9,
                'comment' => 'Phim hoạt hình mà người lớn xem xong cũng phải suy ngẫm nhiều. Thông điệp chữa lành sâu sắc, tạo hình nhân vật siêu dễ thương. Cả rạp nhiều đoạn cười òa rồi lại sụt sùi xúc động.',
                'likes' => 35,
            ],
            [
                'rating' => 9,
                'comment' => 'Phim giải trí đỉnh chóp cho dịp cuối tuần! Dàn diễn viên duyên dáng, nhạc phim bắt tai. Mua combo bắp rang phô mai 2 ngăn ăn kèm xem phim hết sảy.',
                'likes' => 22,
            ],
            [
                'rating' => 9,
                'comment' => 'Nhịp phim dồn dập, các cú twist bất ngờ không đoán trước được. Màu phim điện ảnh u tối rất có gu. Rất đề xuất cho các bạn mê thể loại giật gân.',
                'likes' => 27,
            ],

            // 🎯 Đánh giá 8 sao (Hay / Giải trí tốt)
            [
                'rating' => 8,
                'comment' => 'Nhịp phim ban đầu hơi chậm một chút để xây dựng tâm lý nhưng càng về sau càng bùng nổ. Pha hành động mãn nhãn, bắp nước rạp Landmark 81 hôm nay cũng rất giòn và ngon.',
                'likes' => 19,
            ],
            [
                'rating' => 8,
                'comment' => 'Phim kinh dị giật gân làm mình thót tim mấy phen. Không khí rạp tối kết hợp âm thanh Surround làm tăng độ rùng rợn gấp 10 lần. Rất đáng tiền vé!',
                'likes' => 24,
            ],
            [
                'rating' => 8,
                'comment' => 'Phim truyền cảm hứng mạnh mẽ. Kịch bản chặt chẽ, diễn viên phụ tròn vai. Một lựa chọn tuyệt vời cho ngày hẹn hò cuối tuần.',
                'likes' => 16,
            ],
            [
                'rating' => 8,
                'comment' => 'Kỹ xảo 3D đẹp mắt, góc quay ấn tượng. Thời lượng hơn 2 tiếng trôi qua rất nhanh. Nhân viên rạp phục vụ chu đáo, soát vé QR vào rạp cực nhanh.',
                'likes' => 18,
            ],

            // 👍 Đánh giá 7 sao (Khá tốt)
            [
                'rating' => 7,
                'comment' => 'Nội dung ở mức khá tốt, phần nhìn 10 điểm nhưng cái kết hơi vội một chút. Dù sao vẫn là một trải nghiệm giải trí cuối tuần tuyệt vời cùng gia đình.',
                'likes' => 11,
            ],
            [
                'rating' => 7,
                'comment' => 'Phim có vài hạt sạn nhỏ về kịch bản nhưng bù lại diễn xuất và âm nhạc đã cứu vớt lại tất cả. Đặt vé online nhận mã giảm giá 30k thấy khá hời.',
                'likes' => 8,
            ],
        ];

        $users = User::where('role', 'user')->get();
        $movies = Movie::all();

        if ($movies->isEmpty() || $users->isEmpty()) {
            return;
        }

        MovieReview::truncate();

        foreach ($movies as $movie) {
            // Mỗi phim sinh 4 - 8 review ngẫu nhiên đa dạng
            $reviewCount = min(rand(4, 7), count($reviewsPool));
            $selectedReviews = collect($reviewsPool)->random($reviewCount);

            foreach ($selectedReviews as $rev) {
                $user = $users->random();

                MovieReview::create([
                    'movie_id' => $movie->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_avatar' => $user->avatar,
                    'membership_tier' => $user->membership_tier ?: 'member',
                    'rating' => $rev['rating'],
                    'comment' => $rev['comment'],
                    'likes_count' => $rev['likes'] + rand(0, 15),
                    'created_at' => now()->subHours(rand(1, 120)),
                ]);
            }
        }
    }
}
